{{-- Shared expense form fields --}}
<div class="mb-3">
    <label class="form-label fw-medium">Title <span class="text-danger">*</span></label>
    <input type="text" name="title" class="form-control" required maxlength="255"
           value="{{ old('title') }}" placeholder="e.g. Ground rent for June 2026">
</div>

<div class="row g-2 mb-3">
    <div class="col-sm-6">
        <label class="form-label fw-medium">Category <span class="text-danger">*</span></label>
        <select name="category" class="form-select" required>
            @foreach($categoryOptions as $val => $label)
                <option value="{{ $val }}" {{ old('category') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-6">
        <label class="form-label fw-medium">Amount (৳) <span class="text-danger">*</span></label>
        <input type="number" name="amount" class="form-control" min="0.01" step="0.01"
               value="{{ old('amount') }}" required>
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="col-sm-6">
        <label class="form-label fw-medium">Expense Date <span class="text-danger">*</span></label>
        <input type="date" name="expense_date" class="form-control"
               value="{{ old('expense_date', date('Y-m-d')) }}" required>
    </div>
    <div class="col-sm-6">
        <label class="form-label fw-medium">Receipt / Reference</label>
        <input type="text" name="receipt_reference" class="form-control" maxlength="100"
               value="{{ old('receipt_reference') }}" placeholder="Optional">
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-medium">Notes</label>
    <textarea name="notes" class="form-control" rows="2" maxlength="500"
              placeholder="Optional details">{{ old('notes') }}</textarea>
</div>
