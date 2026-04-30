<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonthlyDonation;
use App\Models\Payment;
use App\Models\Player;
use App\Models\Tournament;
use App\Services\FinanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function __construct(private FinanceService $financeService)
    {
        $this->middleware('can:finance-view')->only(['dashboard']);
        $this->middleware('can:finance-dues-manage')->only(['dues']);
        $this->middleware('can:finance-payments-manage')->only(['createPayment', 'payments']);
        $this->middleware('can:finance-expenses-manage')->only(['expenses']);
        $this->middleware('can:finance-categories-manage')->only(['categories']);
        $this->middleware('can:finance-reports-view')->only(['reports']);
    }

    private array $categories = [
        'donation' => ['label' => 'Monthly Donation', 'default_amount' => 0, 'recurrence' => 'Monthly', 'active' => true],
        'tournament' => ['label' => 'Tournament Fee', 'default_amount' => 0, 'recurrence' => 'Per tournament', 'active' => true],
        'jersey' => ['label' => 'Jersey Fee', 'default_amount' => 0, 'recurrence' => 'One time', 'active' => true],
        'other' => ['label' => 'Other Collection', 'default_amount' => 0, 'recurrence' => 'As needed', 'active' => true],
    ];

    public function dashboard()
    {
        $this->financeService->ensureMonthlyDonationDuesForCurrentMonth();
        $this->setTitle('Finance Dashboard', 'Dashboard');

        $payments = Payment::with(['player.user', 'tournament'])->latest('payment_date')->get();
        $dues = MonthlyDonation::with('player.user')->latest()->get();
        $monthPayments = $payments->filter(fn ($payment) => $payment->payment_date && Carbon::parse($payment->payment_date)->isCurrentMonth());
        $unpaidDues = $dues->where('is_paid', false);

        $dashboard = [
            'total_received' => $payments->where('status', 'paid')->sum('amount'),
            'total_due' => $dues->sum('expected_amount'),
            'total_expense' => 0,
            'current_balance' => $payments->where('status', 'paid')->sum('amount'),
            'monthly_received' => $monthPayments->where('status', 'paid')->sum('amount'),
            'monthly_expenses' => 0,
            'overdue_players' => $unpaidDues->pluck('player_id')->unique()->count(),
            'pending_dues' => $unpaidDues->count(),
        ];
        $currentMonthDonation = $this->financeService->currentMonthDonationSummary();

        $monthlyIncome = collect(range(1, 12))->map(fn ($month) => (float) Payment::whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', $month)
            ->where('status', 'paid')
            ->sum('amount'));

        $topDefaulters = $dues->groupBy('player_id')->map(function ($items) {
            $player = $items->first()->player;
            return [
                'player' => $player,
                'due_count' => $items->where('is_paid', false)->count(),
                'remaining' => $items->sum('expected_amount') - $items->sum('paid_amount'),
            ];
        })->sortByDesc('remaining')->take(8);

        return view('admin.pages.finance.dashboard', [
            'dashboard' => $dashboard,
            'recentPayments' => $payments->take(8),
            'recentExpenses' => collect(),
            'topDefaulters' => $topDefaulters,
            'monthlyIncome' => $monthlyIncome,
            'monthlyExpense' => collect(range(1, 12))->map(fn () => 0),
            'currentMonthDonation' => $currentMonthDonation,
        ]);
    }

    public function dues(Request $request)
    {
        $this->financeService->ensureMonthlyDonationDuesForCurrentMonth();
        $this->setTitle('Player Dues', 'Player Dues');
        $currentMonth = now();

        $dues = MonthlyDonation::with('player.user')
            ->when($request->player_id, fn ($query) => $query->where('player_id', $request->player_id))
            ->when($request->current_month_donation, fn ($query) => $query->where('year', $currentMonth->year)->where('month', $currentMonth->month))
            ->when($request->status, function ($query) use ($request) {
                if ($request->status === 'paid') {
                    $query->where('is_paid', true);
                }
                if ($request->status === 'partial') {
                    $query->where('is_paid', false)->where('paid_amount', '>', 0);
                }
                if ($request->status === 'pending') {
                    $query->where('is_paid', false)->where('paid_amount', 0);
                }
                if ($request->status === 'overdue') {
                    $query->where('is_paid', false)->where(function ($subQuery) {
                        $subQuery->where('year', '<', now()->year)
                            ->orWhere(function ($monthQuery) {
                                $monthQuery->where('year', now()->year)->where('month', '<', now()->month);
                            });
                    });
                }
            })
            ->whereHas('player.user', fn ($query) => $query->where('status', 'active'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.pages.finance.dues.index', [
            'dues' => $dues,
            'players' => Player::with('user')->orderBy('id')->get(),
            'tournaments' => Tournament::orderBy('name')->get(),
            'categories' => $this->categories,
        ]);
    }

    public function createPayment(Request $request)
    {
        $this->financeService->ensureMonthlyDonationDuesForCurrentMonth();
        $this->setTitle('Receive Payment', 'Receive Payment');

        $players = Player::with(['user', 'monthlyDonations' => fn ($query) => $query->where('is_paid', false)->latest()])
            ->whereHas('user', fn ($query) => $query->where('status', 'active'))
            ->get();
        $selectedDue = $request->due_id ? MonthlyDonation::with('player.user')->find($request->due_id) : null;

        return view('admin.pages.finance.payments.create', [
            'players' => $players,
            'selectedDue' => $selectedDue,
            'tournaments' => Tournament::orderBy('name')->get(),
            'categories' => $this->categories,
        ]);
    }

    public function payments(Request $request)
    {
        $this->setTitle('Payment History', 'Payment History');

        $payments = Payment::with(['player.user', 'tournament'])
            ->when($request->player_id, fn ($query) => $query->where('player_id', $request->player_id))
            ->when($request->category, fn ($query) => $query->where('type', $request->category))
            ->when($request->start_date, fn ($query) => $query->whereDate('payment_date', '>=', $request->start_date))
            ->when($request->end_date, fn ($query) => $query->whereDate('payment_date', '<=', $request->end_date))
            ->latest('payment_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pages.finance.payments.index', [
            'payments' => $payments,
            'players' => Player::with('user')->get(),
            'categories' => $this->categories,
        ]);
    }

    public function expenses(Request $request)
    {
        $this->setTitle('Expenses', 'Expenses');

        return view('admin.pages.finance.expenses.index', [
            'expenses' => collect(),
            'tournaments' => Tournament::orderBy('name')->get(),
        ]);
    }

    public function categories()
    {
        $this->setTitle('Payment Categories', 'Categories');

        return view('admin.pages.finance.categories.index', [
            'categories' => $this->categories,
        ]);
    }

    public function reports(Request $request)
    {
        $this->financeService->ensureMonthlyDonationDuesForCurrentMonth();
        $this->setTitle('Finance Reports', 'Reports');

        $payments = Payment::with(['player.user', 'tournament'])
            ->when($request->start_date, fn ($query) => $query->whereDate('payment_date', '>=', $request->start_date))
            ->when($request->end_date, fn ($query) => $query->whereDate('payment_date', '<=', $request->end_date))
            ->get();

        return view('admin.pages.finance.reports.index', [
            'payments' => $payments,
            'playerSummary' => $payments->groupBy('player_id'),
            'categorySummary' => $payments->groupBy('type'),
            'tournamentSummary' => $payments->whereNotNull('tournament_id')->groupBy('tournament_id'),
            'totalIncome' => $payments->where('status', 'paid')->sum('amount'),
            'totalExpenses' => 0,
        ]);
    }

    private function setTitle(string $title, string $current): void
    {
        session([
            'title' => $title,
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['title' => $current, 'url' => url()->current()],
            ],
        ]);
    }
}
