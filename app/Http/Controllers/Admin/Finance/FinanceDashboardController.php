<?php

namespace App\Http\Controllers\Admin\Finance;

use Exception;
use App\Models\Expense;
use App\Models\PlayerPayment;
use App\Models\PlayerPaymentDue;
use App\Services\FinanceService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class FinanceDashboardController extends Controller
{
    protected string $module = 'finance';

    public function __construct(protected FinanceService $finance) {}

    public function index()
    {
        try {
            if (!Auth::user()->can($this->module . '-view')) {
                throw new Exception('Unauthorized Access');
            }

            session([
                'title'       => 'Finance Dashboard',
                'breadcrumbs' => [
                    'home'    => ['url' => route('admin.dashboard'),        'name' => 'Dashboard'],
                    'finance' => ['url' => route('admin.finance.dashboard'), 'name' => 'Finance'],
                ],
            ]);

            $summary     = $this->finance->getOverallSummary();
            $chartData   = $this->finance->getMonthlyCollectionData(6);
            $defaulters  = $this->finance->getTopDefaulters(10);

            // Current-month stats
            $thisMonth = Carbon::now();
            $summary['monthly_collected'] = (float) PlayerPayment::whereMonth('payment_date', $thisMonth->month)
                ->whereYear('payment_date', $thisMonth->year)->sum('amount');
            $summary['monthly_expenses']  = (float) Expense::whereMonth('expense_date', $thisMonth->month)
                ->whereYear('expense_date', $thisMonth->year)->sum('amount');

            // Recent payments
            $recentPayments = PlayerPayment::with(['player.user', 'category'])
                ->orderByDesc('payment_date')
                ->limit(10)
                ->get();

            // Recent expenses
            $recentExpenses = Expense::with('paidBy')
                ->orderByDesc('expense_date')
                ->limit(8)
                ->get();

            // Overdue dues
            $overdueDues = PlayerPaymentDue::with(['player.user', 'category'])
                ->overdue()
                ->orderBy('due_date')
                ->limit(15)
                ->get();

            return view('admin.pages.finance.dashboard', compact(
                'summary',
                'chartData',
                'defaulters',
                'recentPayments',
                'recentExpenses',
                'overdueDues'
            ));

        } catch (Exception $e) {
            Log::error('Finance dashboard error', ['message' => $e->getMessage()]);
            return redirect()->back()->with(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
