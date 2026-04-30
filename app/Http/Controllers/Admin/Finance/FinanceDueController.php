<?php

namespace App\Http\Controllers\Admin\Finance;

use Exception;
use App\Models\Player;
use App\Models\PaymentCategory;
use App\Models\PlayerPaymentDue;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class FinanceDueController extends Controller
{
    protected string $module = 'finance-dues';

    public function __construct(protected FinanceService $finance) {}

    // ─── Index ────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        try {
            if (!Auth::user()->can('finance-view')) {
                throw new Exception('Unauthorized Access');
            }

            session([
                'title'       => 'Player Dues',
                'breadcrumbs' => [
                    'home'    => ['url' => route('admin.dashboard'),        'name' => 'Dashboard'],
                    'finance' => ['url' => route('admin.finance.dashboard'), 'name' => 'Finance'],
                    'dues'    => ['url' => route('admin.finance.dues.index'), 'name' => 'Dues'],
                ],
            ]);

            $query = PlayerPaymentDue::with(['player.user', 'category'])
                ->orderByDesc('due_date');

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('player_id')) {
                $query->where('player_id', $request->player_id);
            }
            if ($request->filled('period_label')) {
                $query->where('period_label', $request->period_label);
            }

            $dues       = $query->paginate(25)->withQueryString();
            $categories = PaymentCategory::active()->orderBy('sort_order')->get();

            $dueStats = [
                'total'   => PlayerPaymentDue::count(),
                'pending' => PlayerPaymentDue::whereIn('status', ['pending', 'partial'])->count(),
                'overdue' => PlayerPaymentDue::whereIn('status', ['pending', 'partial'])
                                ->whereDate('due_date', '<', now())->count(),
                'paid'    => PlayerPaymentDue::where('status', 'paid')->count(),
                'waived'  => PlayerPaymentDue::where('status', 'waived')->count(),
            ];

            return view('admin.pages.finance.dues.index', compact('dues', 'categories', 'dueStats'));

        } catch (Exception $e) {
            Log::error('FinanceDue index error', ['message' => $e->getMessage()]);
            return redirect()->back()->with(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── Bulk-assign ──────────────────────────────────────────────────

    public function bulkAssign(Request $request)
    {
        try {
            if (!Auth::user()->can($this->module . '-manage')) {
                throw new Exception('Unauthorized Access');
            }

            $validator = Validator::make($request->all(), [
                'category_id'   => 'required|exists:payment_categories,id',
                'amount'        => 'required|numeric|min:0.01',
                'due_date'      => 'nullable|date',
                'period_label'  => 'required|string|max:100',
                'notes'         => 'nullable|string|max:500',
                'player_ids'    => 'nullable|array',
                'player_ids.*'  => 'integer|exists:players,id',
                'assign_all'    => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $playerIds = $request->boolean('assign_all')
                ? []
                : array_map('intval', $request->player_ids ?? []);

            $created = $this->finance->bulkAssignDues(
                (int)   $request->category_id,
                (float) $request->amount,
                $request->due_date,
                $request->period_label,
                $request->notes,
                $playerIds
            );

            return redirect()
                ->route('admin.finance.dues.index')
                ->with(['success' => true, 'message' => "{$created} due(s) assigned successfully."]);

        } catch (Exception $e) {
            Log::error('FinanceDue bulkAssign error', ['message' => $e->getMessage()]);
            return redirect()->back()->withInput()->with(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── Store single ─────────────────────────────────────────────────

    public function store(Request $request)
    {
        try {
            if (!Auth::user()->can($this->module . '-manage')) {
                throw new Exception('Unauthorized Access');
            }

            $validator = Validator::make($request->all(), [
                'player_id'    => 'required|exists:players,id',
                'category_id'  => 'required|exists:payment_categories,id',
                'amount'       => 'required|numeric|min:0.01',
                'due_date'     => 'nullable|date',
                'period_label' => 'nullable|string|max:100',
                'notes'        => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            PlayerPaymentDue::create([
                'player_id'    => $request->player_id,
                'category_id'  => $request->category_id,
                'amount'       => $request->amount,
                'due_date'     => $request->due_date,
                'period_label' => $request->period_label,
                'notes'        => $request->notes,
                'created_by'   => Auth::id(),
            ]);

            return redirect()
                ->route('admin.finance.dues.index')
                ->with(['success' => true, 'message' => 'Due created successfully.']);

        } catch (Exception $e) {
            Log::error('FinanceDue store error', ['message' => $e->getMessage()]);
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

            $due = PlayerPaymentDue::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'amount'       => 'required|numeric|min:0',
                'due_date'     => 'nullable|date',
                'period_label' => 'nullable|string|max:100',
                'notes'        => 'nullable|string|max:500',
                'status'       => 'required|in:pending,partial,paid,waived',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $due->update([
                'amount'       => $request->amount,
                'due_date'     => $request->due_date,
                'period_label' => $request->period_label,
                'notes'        => $request->notes,
                'status'       => $request->status,
            ]);

            return redirect()
                ->route('admin.finance.dues.index')
                ->with(['success' => true, 'message' => 'Due updated.']);

        } catch (Exception $e) {
            Log::error('FinanceDue update error', ['message' => $e->getMessage()]);
            return redirect()->back()->withInput()->with(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── Waive ────────────────────────────────────────────────────────

    public function waive(Request $request, int $id)
    {
        try {
            if (!Auth::user()->can($this->module . '-manage')) {
                throw new Exception('Unauthorized Access');
            }

            $due = PlayerPaymentDue::findOrFail($id);
            $this->finance->waiveDue($due, $request->waive_notes);

            return response()->json(['success' => true, 'message' => 'Due waived.']);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ─── Destroy ──────────────────────────────────────────────────────

    public function destroy(int $id)
    {
        try {
            if (!Auth::user()->can($this->module . '-manage')) {
                throw new Exception('Unauthorized Access');
            }

            $due = PlayerPaymentDue::findOrFail($id);

            if ($due->payments()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete: payments have been recorded against this due.',
                ], 422);
            }

            $due->delete();

            return response()->json(['success' => true, 'message' => 'Due deleted.']);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
