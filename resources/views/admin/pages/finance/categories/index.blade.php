@extends('admin.layouts.theme')

@push('styles')
    @include('admin.pages.finance.partials.styles')
@endpush

@section('content')
<div class="finance-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div><h3 class="mb-1">Payment Categories</h3><p class="text-muted mb-0">Clean category overview for dues and collections.</p></div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal"><i class="ri-add-line me-1"></i>Add Category</button>
    </div>

    <div class="row g-3">
        @foreach($categories as $key => $category)
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="finance-icon bg-primary-subtle text-primary"><i class="ri-price-tag-3-line"></i></div>
                            <span class="badge {{ $category['active'] ? 'bg-success' : 'bg-secondary' }}">{{ $category['active'] ? 'Active' : 'Inactive' }}</span>
                        </div>
                        <h5>{{ $category['label'] }}</h5>
                        <div class="small text-muted mb-3">{{ ucfirst($key) }}</div>
                        <div class="d-flex justify-content-between border-top pt-2"><span>Default Amount</span><strong>{{ number_format($category['default_amount'], 2) }}</strong></div>
                        <div class="d-flex justify-content-between mt-2"><span>Recurrence</span><strong>{{ $category['recurrence'] }}</strong></div>
                        <div class="finance-action mt-3">
                            <button class="btn btn-sm btn-outline-primary" disabled>Edit</button>
                            <button class="btn btn-sm btn-outline-secondary" disabled>Toggle</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Name</label><input class="form-control"></div>
            <div class="mb-3"><label class="form-label">Default Amount</label><input type="number" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Recurrence Type</label><select class="form-select"><option>Monthly</option><option>One time</option><option>Per tournament</option><option>As needed</option></select></div>
            <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="activeCategory" checked><label class="form-check-label" for="activeCategory">Active</label></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button class="btn btn-primary" disabled>Save Category</button></div>
    </div></div>
</div>
@endsection
