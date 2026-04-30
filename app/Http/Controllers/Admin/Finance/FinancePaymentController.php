<?php

namespace App\Http\Controllers\Admin\Finance;

use Exception;
use App\Models\Player;
use App\Models\PaymentCategory;
use App\Models\PlayerPayment;
use App\Models\PlayerPaymentDue;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class FinancePaymentController extends Controller
{
    protected string $module = 'finance-payments';

    public function __construct(protected FinanceService $finance) {}

    // ─── Index ────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        try {
            if (!Auth::user()->can('finance-view')) {
                throw new Exception('Unauthorized Access');
            }

            session([
                'title'       => 'Payment Collection',
                'breadcrumbs' => [
                    'home'     => ['url' => route('admin.dashboard'),          'name' => 'Dashboard'],
                    'finance'  => ['url' => route('admin.finance.dashboard'),   'name' => 'Finance'],
                    'payments' => ['url' => route('admin.finance.payments.index'), 'name' => 'Payments'],
                ],
            ]);

            $query = PlayerPayment::with(['player.user', 'category', 'due', 'collectedBy'])
                ->orderByDesc('payment_date');

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }
            if ($request->filled('player_id')) {
                $query->where('player_id', $request->player_id);
            }
            if ($request->filled('month')) {
                $query->whereMonth('payment_date', $request->month)
                      ->whereYear('payment_date', $request->year ?? now()->year);
            }
            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }

            $payments   = $query->paginate(25)->withQueryString();
            $categories = PaymentCategory::active()->orderBy('sort_order')->get();
            $totalShown = PlayerPayment::when($request->filled('month'), fn($q) =>
                    $q->whereMonth('payment_date', $request->month)
                      ->whereYear('payment_date', $request->year ?? now()->year))
                ->sum('amount');

            return view('admin.pages.finance.payments.index', compact(
                'payments', 'categories', 'totalShown'
            ));

        } catch (Exception $e) {
            Log::error('FinancePayment index error', ['message' => $e->getMessage()]);
            return redirect()->back()->with(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── Store ────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        try {
            if (!Auth::user()->can($this->module . '-manage')) {
                throw new Exception('Unauthorized Access');
            }

            $validator = Validator::make($request->all(), [
                'player_id'      => 'required|exists:players,id',
                'category_id'    => 'required|exists:payment_categories,id',
                'due_id'         => 'nullable|exists:player_payment_dues,id',
                'amount'         => 'required|numeric|min:0.01',
                'payment_date'   => 'required|date',
                'payment_method' => 'required|in:cash,bkash,nagad,bank_transfer,other',
                'reference'      => 'nullable|string|max:100',
                'notes'          => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $this->finance->collectPayment($request->only([
                'player_id', 'category_id', 'due_id',
                'amount', 'payment_date', 'payment_method',
                'reference', 'notes',
            ]));

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Payment recorded.']);
            }

            return redirect()
                ->route('admin.finance.payments.index')
                ->with(['success' => true, 'message' => 'Payment recorded successfully.']);

        } catch (Exception $e) {
            Log::error('FinancePayment store error', ['message' => $e->getMessage()]);
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->with(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── Destroy ──────────────────────────────────────────────────────

    public function destroy(int $id)
    {
        try {
            if (!Auth::user()->can($this->module . '-manage')) {
                throw new Exception('Unauthorized Access');
            }

            $payment = PlayerPayment::findOrFail($id);
            $this->finance->deletePayment($payment);

            return response()->json(['success' => true, 'message' => 'Payment deleted.']);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ─── Search players (AJAX for Select2) ───────────────────────────

    public function searchPlayers(Request $request)
    {
        $q = trim($request->get('q', ''));
        $players = Player::with('user')
            ->whereHas('user', function ($query) use ($q) {
                $query->where('status', 'active');
                if ($q) {
                    $query->where(function ($sub) use ($q) {
                        $sub->where('full_name', 'LIKE', "%{$q}%")
                            ->orWhere('phone', 'LIKE', "%{$q}%");
                    });
                }
            })
            ->orderBy('id')
            ->limit(20)
            ->get()
            ->map(fn($p) => [
                'id'   => $p->id,
                'text' => ($p->user->full_name ?? 'Unknown') . ($p->user->phone ? ' · ' . $p->user->phone : ''),
            ]);

        return response()->json(['results' => $players->values()]);
    }

    // ─── Get dues for player (AJAX) ───────────────────────────────────

    public function getDuesForPlayer(Request $request)
    {
        try {
            $player = Player::findOrFail($request->player_id);

            $dues = PlayerPaymentDue::with('category')
                ->where('player_id', $player->id)
                ->whereIn('status', ['pending', 'partial'])
                ->get()
                ->map(fn($d) => [
                    'id'              => $d->id,
                    'label'           => $d->category->name . ($d->period_label ? " ({$d->period_label})" : ''),
                    'amount'          => $d->amount,
                    'remaining'       => $d->remaining_amount,
                    'category_id'     => $d->category_id,
                ]);

            return response()->json(['success' => true, 'dues' => $dues]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
