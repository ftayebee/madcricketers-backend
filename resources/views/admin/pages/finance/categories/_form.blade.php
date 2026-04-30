{{-- Shared form fields for create/edit category modal --}}
@if($errors->any())
    <div class="alert alert-danger py-2">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-3">
    <label class="form-label fw-medium">Category Name <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" required maxlength="120"
           value="{{ old('name') }}" placeholder="e.g. Monthly Due, Jersey Fee">
</div>

<div class="mb-3">
    <label class="form-label fw-medium">Description</label>
    <textarea name="description" class="form-control" rows="2" maxlength="500"
              placeholder="Optional description">{{ old('description') }}</textarea>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6">
        <label class="form-label fw-medium">Recurrence <span class="text-danger">*</span></label>
        <select name="recurrence_type" class="form-select" required>
            <option value="one_time"  {{ old('recurrence_type') === 'one_time'  ? 'selected' : '' }}>One-time</option>
            <option value="monthly"   {{ old('recurrence_type') === 'monthly'   ? 'selected' : '' }}>Monthly</option>
            <option value="annual"    {{ old('recurrence_type') === 'annual'    ? 'selected' : '' }}>Annual</option>
        </select>
    </div>
    <div class="col-sm-6">
        <label class="form-label fw-medium">Default Amount (৳) <span class="text-danger">*</span></label>
        <input type="number" name="default_amount" class="form-control" min="0" step="0.01"
               value="{{ old('default_amount', 0) }}" required>
    </div>
</div>

<div class="row g-3">
    <div class="col-sm-6">
        <label class="form-label fw-medium">Sort Order</label>
        <input type="number" name="sort_order" class="form-control" min="0"
               value="{{ old('sort_order', 0) }}">
    </div>
    <div class="col-sm-6 d-flex align-items-center pt-4">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active_check" value="1"
                   {{ old('is_active', 1) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active_check">Active</label>
        </div>
    </div>
</div>
