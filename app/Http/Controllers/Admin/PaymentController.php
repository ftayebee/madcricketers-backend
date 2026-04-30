<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Player;
use App\Models\Payment;
use App\Models\Tournament;
use Illuminate\Http\Request;
use App\Models\MonthlyDonation;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    private function authorizePayment(string $permission): void
    {
        if (!Auth::user() || !Auth::user()->can($permission)) {
            abort(403, 'Unauthorized Access');
        }
    }

    public function index(Request $request)
    {
        $this->authorizePayment('finance-view');

        return redirect()->route('admin.finance.dashboard');

        session([
            'title' => 'Payments Summary',
            'breadcrumbs' => [
                'home' => [
                    'url' => route('admin.dashboard'),
                    'name' => 'Dashboard'
                ],
                'role' => [
                    'url' => route('admin.payments.index'),
                    'name' => 'Payments Management'
                ]
            ]
        ]);

        $payments = Payment::with(['player', 'tournament'])
            ->when($request->player_id, fn($q) => $q->where('player_id', $request->player_id))
            ->when($request->tournament_id, fn($q) => $q->where('tournament_id', $request->tournament_id))
            ->when($request->month, function ($q) use ($request) {
                $q->whereMonth('payment_date', $request->month);
            })
            ->when($request->year, function ($q) use ($request) {
                $q->whereYear('payment_date', $request->year);
            })
            ->orderBy('payment_date', 'desc')
            ->get();

        return view('admin.pages.payments.index', compact('payments'));
    }

    public function tableLoader(Request $request)
    {
        $this->authorizePayment('finance-payments-manage');

        $month = $request->month ?? now()->month;
        $year  = $request->year ?? now()->year;

        // Subquery for current month payments
        $paymentsSub = Payment::select('id', 'player_id', 'tournament_id', 'type', 'amount', 'status', 'payment_date')
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year);

        $paymentsQuery = Payment::with(['player', 'tournament'])->select('payments.*');

        $playersQuery = Player::query()
            ->leftJoin('users', 'players.user_id', '=', 'users.id')
            ->leftJoinSub($paymentsSub, 'payments', function ($join) {
                $join->on('players.id', '=', 'payments.player_id');
            })
            ->leftJoin('tournaments', 'payments.tournament_id', '=', 'tournaments.id')
            ->select([
                'players.id as player_id',
                'users.full_name as player_name', // 👈 this works
                'payments.id as payment_id',
                'payments.type',
                'payments.amount',
                'payments.status',
                'payments.payment_date',
                'tournaments.name as tournament_name',
            ]);

        // --- Summary / stats calculations ---
        $currentPayments = Payment::whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->get();

        $summary = [
            'donations'    => $currentPayments->where('type', 'donation')->sum('amount'),
            'tournament'   => $currentPayments->where('type', 'tournament')->sum('amount'),
            'registration' => $currentPayments->where('type', 'jersey')->sum('amount'),
            'other'        => $currentPayments->where('type', 'other')->sum('amount'),
        ];

        // player status counts
        $allPlayers = Player::count();
        $paidPlayers = $currentPayments->pluck('player_id')->unique()->count();
        $playerStatus = [
            'paid'    => $paidPlayers,
            'notPaid' => $allPlayers - $paidPlayers,
        ];

        // Day-wise totals for charts 
        $dayWise = [];
        foreach (['donation', 'tournament', 'jersey', 'other'] as $type) {
            $dayWise[$type === 'jersey' ? 'registration' : $type] = $currentPayments->where('type', $type)->groupBy(function ($item) {
                return Carbon::parse($item->payment_date)->format('d');
            })->map(function ($items) {
                return $items->sum('amount');
            })->values()->toArray();
        }

        // Calculate percentage change 
        $prevMonth = $month == 1 ? 12 : $month - 1;
        $prevYear = $month == 1 ? $year - 1 : $year;
        $previousPayments = (clone $paymentsQuery)->whereMonth('payment_date', $prevMonth)->whereYear('payment_date', $prevYear)->get();
        $previousSummary = [
            'donations' => $previousPayments->where('type', 'donation')->sum('amount'),
            'tournament' => $previousPayments->where('type', 'tournament')->sum('amount'),
            'registration' => $previousPayments->where('type', 'jersey')->sum('amount'),
            'other' => $previousPayments->where('type', 'other')->sum('amount'),
        ];

        $percentages = [];
        foreach ($summary as $key => $value) {
            $prev = $previousSummary[$key] ?: 0;
            if ($prev == 0 && $value > 0) {
                $percentages[$key] = 100;
            } elseif ($prev == 0 && $value == 0) {
                $percentages[$key] = 0;
            } else {
                $percentages[$key] = round((($value - $prev) / $prev) * 100, 2);
            }
        }

        return DataTables::of($playersQuery)
            ->editColumn('player_name', fn($row) => $row->player_name ?? '-')
            ->addColumn('actions', function ($row) {
                if (!$row->payment_id) {
                    return '';
                }
                return view('admin.pages.payments.partials.actions', ['p' => $row])->render();
            })
            ->editColumn('payment_date', function ($row) {
                return $row->payment_date ? \Carbon\Carbon::parse($row->payment_date)->format('d M Y') : '-';
            })
            ->with(['summary' => $summary, 'percentages' => $percentages, 'dayWise' => $dayWise, 'playerStatus' => $playerStatus])
            ->rawColumns(['actions'])
            ->make(true);
    }

    // Store a payment
    public function store(Request $request)
    {
        $this->authorizePayment('finance-payments-manage');

        $validator = Validator::make($request->all(), [
            'player_id' => 'required|exists:players,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:donation,tournament,jersey,other',
            'status' => 'required|in:paid,pending',
            'tournament_id' => 'nullable|exists:tournaments,id',
            'payment_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            Log::info($validator->errors());
            if (!$request->expectsJson() && !$request->ajax()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            return response()->json([
                'success' => false,
                'message' => 'Validation Failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $payment = Payment::create($request->all());

        if ($request->filled('due_id') && $payment->status === 'paid') {
            $due = MonthlyDonation::where('id', $request->due_id)
                ->where('player_id', $payment->player_id)
                ->first();

            if ($due) {
                $due->paid_amount = min($due->expected_amount, $due->paid_amount + $payment->amount);
                $due->is_paid = $due->paid_amount >= $due->expected_amount;
                $due->save();
            }
        }

        if ($payment->type === 'donation' && !$request->filled('due_id')) {
            $paymentDate = \Carbon\Carbon::parse($payment->payment_date);

            $due = MonthlyDonation::firstOrCreate(
                [
                    'player_id' => $payment->player_id,
                    'year'      => $paymentDate->year,
                    'month'     => $paymentDate->month,
                ],
                [
                    'expected_amount' => $payment->amount,
                    'paid_amount'     => 0,
                    'is_paid'         => false,
                ]
            );

            if ($payment->status === 'paid') {
                $due->paid_amount = min($due->expected_amount, $due->paid_amount + $payment->amount);
                $due->is_paid = $due->paid_amount >= $due->expected_amount;
                $due->save();
            }
        }

        if (!$request->expectsJson() && !$request->ajax()) {
            return redirect()->route('admin.finance.payments.index')->with([
                'success' => true,
                'message' => 'Payment received successfully.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment added successfully.'
        ]);
    }

    // Monthly donation report
    public function monthlyDonationsReport($month = null, $year = null)
    {
        $this->authorizePayment('finance-reports-view');

        $month = $month ?? date('m');
        $year = $year ?? date('Y');

        $donations = MonthlyDonation::with('player')
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $summary = [
            'total_expected' => $donations->sum('amount'),
            'total_collected' => $donations->where('status', 'paid')->sum('amount'),
            'pending_count' => $donations->where('status', 'pending')->count(),
        ];

        return view('admin.reports.monthly_donations', compact('donations', 'summary', 'month', 'year'));
    }

    // Tournament payments report
    public function tournamentReport($tournament_id)
    {
        $this->authorizePayment('finance-reports-view');

        $tournament = Tournament::findOrFail($tournament_id);
        $payments = Payment::with('player')
            ->where('tournament_id', $tournament_id)
            ->get();

        $summary = [
            'total_expected' => $payments->sum('amount'),
            'total_collected' => $payments->where('status', 'paid')->sum('amount'),
            'pending_count' => $payments->where('status', 'pending')->count(),
        ];

        return view('admin.reports.tournament', compact('tournament', 'payments', 'summary'));
    }

    // Mark payment as paid
    public function markAsPaid($id)
    {
        $this->authorizePayment('finance-payments-manage');

        $payment = Payment::findOrFail($id);
        $payment->status = 'paid';
        $payment->payment_date = Carbon::now();
        $payment->save();

        return redirect()->back()->with('success', 'Payment marked as paid.');
    }

    public function update(Request $request, $id)
    {
        $this->authorizePayment('finance-payments-manage');

        $validator = Validator::make($request->all(), [
            'player_id' => 'required|exists:players,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:donation,tournament,jersey,other',
            'status' => 'required|in:paid,pending',
            'tournament_id' => 'nullable|exists:tournaments,id',
            'payment_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            if (!$request->expectsJson() && !$request->ajax()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            return response()->json([
                'success' => false,
                'message' => 'Validation Failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $payment = Payment::findOrFail($id);
        $payment->update($validator->validated());

        if (!$request->expectsJson() && !$request->ajax()) {
            return redirect()->back()->with([
                'success' => true,
                'message' => 'Payment updated successfully.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully.',
        ]);
    }

    public function summaryIndex()
    {
        $this->authorizePayment('finance-reports-view');

        return redirect()->route('admin.finance.reports.index');

        session([
            'title' => 'Payments Summary',
            'breadcrumbs' => [
                'home' => [
                    'url' => route('admin.dashboard'),
                    'name' => 'Dashboard'
                ],
                'role' => [
                    'url' => route('admin.payments.index'),
                    'name' => 'Payments Management'
                ]
            ]
        ]);

        return view('admin.pages.payments.summary');
    }

    public function summaryData(Request $request)
    {
        $this->authorizePayment('finance-reports-view');

        $query = \App\Models\Payment::query();

        // Apply filters if provided
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start')) {
            $query->whereDate('payment_date', '>=', $request->start);
        }

        if ($request->filled('end')) {
            $query->whereDate('payment_date', '<=', $request->end);
        }

        // Group by type and calculate totals
        $summary = $query->select('type')
            ->selectRaw('SUM(amount) as total')
            ->selectRaw("SUM(CASE WHEN status='paid' THEN amount ELSE 0 END) as paid")
            ->selectRaw("SUM(CASE WHEN status='pending' THEN amount ELSE 0 END) as pending")
            ->groupBy('type')
            ->get();

        return response()->json($summary);
    }

    public function destroy($id)
    {
        $this->authorizePayment('finance-payments-manage');

        $payment = Payment::findOrFail($id);
        $payment->delete();

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Payment deleted successfully.',
        ]);
    }
}
