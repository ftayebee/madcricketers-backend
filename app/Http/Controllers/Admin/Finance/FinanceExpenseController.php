<?php

namespace App\Http\Controllers\Admin\Finance;

use Exception;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class FinanceExpenseController extends Controller
{
    protected string $module = 'finance-expenses';

    // ─── Index ────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        try {
            if (!Auth::user()->can('finance-view')) {
                throw new Exception('Unauthorized Access');
            }

            session([
                'title'       => 'Expenses',
                'breadcrumbs' => [
                    'home'     => ['url' => route('admin.dashboard'),           'name' => 'Dashboard'],
                    'finance'  => ['url' => route('admin.finance.dashboard'),    'name' => 'Finance'],
                    'expenses' => ['url' => route('admin.finance.expenses.index'), 'name' => 'Expenses'],
                ],
            ]);

            $query = Expense::with('paidBy')->orderByDesc('expense_date');

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }
            if ($request->filled('month')) {
                $query->whereMonth('expense_date', $request->month)
                      ->whereYear('expense_date', $request->year ?? now()->year);
            }

            $expenses      = $query->paginate(25)->withQueryString();
            $totalShown    = Expense::when($request->filled('month'), fn($q) =>
                    $q->whereMonth('expense_date', $request->month)
                      ->whereYear('expense_date', $request->year ?? now()->year))
                ->sum('amount');
            $categoryOptions  = Expense::categoryOptions();
            $thisMonthTotal   = Expense::whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year)->sum('amount');
            $thisYearTotal    = Expense::whereYear('expense_date', now()->year)->sum('amount');

            return view('admin.pages.finance.expenses.index', compact(
                'expenses', 'totalShown', 'categoryOptions', 'thisMonthTotal', 'thisYearTotal'
            ));

        } catch (Exception $e) {
            Log::error('FinanceExpense index error', ['message' => $e->getMessage()]);
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
                'title'             => 'required|string|max:255',
                'category'          => 'required|in:' . implode(',', array_keys(Expense::categoryOptions())),
                'amount'            => 'required|numeric|min:0.01',
                'expense_date'      => 'required|date',
                'receipt_reference' => 'nullable|string|max:100',
                'notes'             => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            Expense::create([
                'title'             => $request->title,
                'category'          => $request->category,
                'amount'            => $request->amount,
                'expense_date'      => $request->expense_date,
                'paid_by'           => Auth::id(),
                'receipt_reference' => $request->receipt_reference,
                'notes'             => $request->notes,
            ]);

            return redirect()
                ->route('admin.finance.expenses.index')
                ->with(['success' => true, 'message' => 'Expense recorded successfully.']);

        } catch (Exception $e) {
            Log::error('FinanceExpense store error', ['message' => $e->getMessage()]);
            return redirect()->back()->withInput()->with(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── Update ───────────────────────────────────────────────────────

    public function update(Request $request, int $id)
    {
        try {
            if (!Auth::user()->can($this->module . '-manage')) {
                throw new Exception('Unauthorized Access');
            }

            $expense = Expense::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title'             => 'required|string|max:255',
                'category'          => 'required|in:' . implode(',', array_keys(Expense::categoryOptions())),
                'amount'            => 'required|numeric|min:0.01',
                'expense_date'      => 'required|date',
                'receipt_reference' => 'nullable|string|max:100',
                'notes'             => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $expense->update([
                'title'             => $request->title,
                'category'          => $request->category,
                'amount'            => $request->amount,
                'expense_date'      => $request->expense_date,
                'receipt_reference' => $request->receipt_reference,
                'notes'             => $request->notes,
            ]);

            return redirect()
                ->route('admin.finance.expenses.index')
                ->with(['success' => true, 'message' => 'Expense updated.']);

        } catch (Exception $e) {
            Log::error('FinanceExpense update error', ['message' => $e->getMessage()]);
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

            Expense::findOrFail($id)->delete();

            return response()->json(['success' => true, 'message' => 'Expense deleted.']);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
