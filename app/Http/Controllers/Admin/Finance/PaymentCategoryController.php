<?php

namespace App\Http\Controllers\Admin\Finance;

use Exception;
use App\Models\PaymentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class PaymentCategoryController extends Controller
{
    protected string $module = 'finance-categories';

    // ─── Index ────────────────────────────────────────────────────────

    public function index()
    {
        try {
            if (!Auth::user()->can('finance-view')) {
                throw new Exception('Unauthorized Access');
            }

            session([
                'title'       => 'Payment Categories',
                'breadcrumbs' => [
                    'home'       => ['url' => route('admin.dashboard'),              'name' => 'Dashboard'],
                    'finance'    => ['url' => route('admin.finance.dashboard'),       'name' => 'Finance'],
                    'categories' => ['url' => route('admin.finance.categories.index'), 'name' => 'Categories'],
                ],
            ]);

            $categories = PaymentCategory::orderBy('sort_order')->orderBy('name')->get();

            return view('admin.pages.finance.categories.index', compact('categories'));

        } catch (Exception $e) {
            Log::error('PaymentCategory index error', ['message' => $e->getMessage()]);
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
                'name'             => 'required|string|max:120',
                'description'      => 'nullable|string|max:500',
                'recurrence_type'  => 'required|in:monthly,annual,one_time',
                'default_amount'   => 'required|numeric|min:0',
                'is_active'        => 'nullable|boolean',
                'sort_order'       => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            PaymentCategory::create([
                'name'            => $request->name,
                'description'     => $request->description,
                'recurrence_type' => $request->recurrence_type,
                'default_amount'  => $request->default_amount,
                'is_active'       => $request->boolean('is_active', true),
                'sort_order'      => $request->sort_order ?? 0,
            ]);

            return redirect()
                ->route('admin.finance.categories.index')
                ->with(['success' => true, 'message' => 'Category created successfully.']);

        } catch (Exception $e) {
            Log::error('PaymentCategory store error', ['message' => $e->getMessage()]);
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

            $category = PaymentCategory::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name'            => 'required|string|max:120',
                'description'     => 'nullable|string|max:500',
                'recurrence_type' => 'required|in:monthly,annual,one_time',
                'default_amount'  => 'required|numeric|min:0',
                'is_active'       => 'nullable|boolean',
                'sort_order'      => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $category->update([
                'name'            => $request->name,
                'description'     => $request->description,
                'recurrence_type' => $request->recurrence_type,
                'default_amount'  => $request->default_amount,
                'is_active'       => $request->boolean('is_active', true),
                'sort_order'      => $request->sort_order ?? 0,
            ]);

            return redirect()
                ->route('admin.finance.categories.index')
                ->with(['success' => true, 'message' => 'Category updated successfully.']);

        } catch (Exception $e) {
            Log::error('PaymentCategory update error', ['message' => $e->getMessage()]);
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

            $category = PaymentCategory::findOrFail($id);

            if ($category->dues()->exists() || $category->payments()->exists()) {
                return redirect()->back()->with([
                    'success' => false,
                    'message' => 'Cannot delete: this category has dues or payments linked to it.',
                ]);
            }

            $category->delete();

            return redirect()
                ->route('admin.finance.categories.index')
                ->with(['success' => true, 'message' => 'Category deleted.']);

        } catch (Exception $e) {
            Log::error('PaymentCategory destroy error', ['message' => $e->getMessage()]);
            return redirect()->back()->with(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── Toggle active ────────────────────────────────────────────────

    public function toggleActive(int $id)
    {
        try {
            if (!Auth::user()->can($this->module . '-manage')) {
                throw new Exception('Unauthorized Access');
            }

            $category = PaymentCategory::findOrFail($id);
            $category->is_active = !$category->is_active;
            $category->save();

            return response()->json([
                'success'   => true,
                'is_active' => $category->is_active,
                'message'   => 'Category ' . ($category->is_active ? 'activated' : 'deactivated') . '.',
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
