{{-- Shared due fields (category, amount, due_date, period_label, notes) --}}
<div class="mb-3">
    <label class="form-label fw-medium">Category <span class="text-danger">*</span></label>
    <select name="category_id" class="form-select" required>
        <option value="">Select Category</option>
        @foreach(\App\Models\PaymentCategory::active()->orderBy('sort_order')->get() as $cat)
            <option value="{{ $cat->id }}" data-amount="{{ $cat->default_amount }}">
                {{ $cat->name }} (৳{{ number_format($cat->default_amount, 0) }})
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label fw-medium">Amount (৳) <span class="text-danger">*</span></label>
    <input type="number" name="amount" class="form-control due-amount-input"
           min="0.01" step="0.01" required placeholder="0.00">
</div>

<div class="row g-2 mb-3">
    <div class="col-sm-6">
        <label class="form-label fw-medium">Due Date</label>
        <input type="date" name="due_date" class="form-control">
    </div>
    <div class="col-sm-6">
        <label class="form-label fw-medium">Period Label <span class="text-danger">*</span></label>
        <input type="text" name="period_label" class="form-control" required
               placeholder="e.g. January 2026" maxlength="100">
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-medium">Notes</label>
    <textarea name="notes" class="form-control" rows="2" maxlength="500"></textarea>
</div>

@push('scripts')
<script>
// Auto-fill amount from category default
document.querySelectorAll('[name="category_id"]').forEach(sel => {
    sel.addEventListener('change', function () {
        const opt    = this.options[this.selectedIndex];
        const amount = opt.dataset.amount;
        const input  = this.closest('form').querySelector('.due-amount-input');
        if (input && amount && parseFloat(amount) > 0) {
            input.value = parseFloat(amount).toFixed(2);
        }
    });
});
</script>
@endpush
