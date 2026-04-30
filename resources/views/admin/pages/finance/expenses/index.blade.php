@extends('admin.layouts.theme')

@push('styles')
    @include('admin.pages.finance.partials.styles')
@endpush

@section('content')
<div class="finance-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div><h3 class="mb-1">Expenses</h3><p class="text-muted mb-0">Tournament and club expense workspace.</p></div>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#expenseModal"><i class="ri-add-line me-1"></i>Add Expense</button>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6 col-xl-3">@include('admin.pages.finance.partials.summary-card', ['label' => 'Total Expenses', 'value' => '0.00', 'color' => 'danger', 'icon' => 'ri-wallet-3-line'])</div>
        <div class="col-md-6 col-xl-3">@include('admin.pages.finance.partials.summary-card', ['label' => 'This Month Expenses', 'value' => '0.00', 'color' => 'danger', 'icon' => 'ri-calendar-line'])</div>
        <div class="col-md-6 col-xl-3">@include('admin.pages.finance.partials.summary-card', ['label' => 'Tournament Expenses', 'value' => '0.00', 'color' => 'warning', 'icon' => 'ri-trophy-line'])</div>
        <div class="col-md-6 col-xl-3">@include('admin.pages.finance.partials.summary-card', ['label' => 'Other Expenses', 'value' => '0.00', 'color' => 'secondary', 'icon' => 'ri-more-line'])</div>
    </div>

    <form class="finance-filter mb-3">
        <div class="row g-2">
            <div class="col-md-3"><label class="form-label">Category</label><select class="form-select"><option>All categories</option><option>Tournament</option><option>Equipment</option><option>Ground</option><option>Other</option></select></div>
            <div class="col-md-3"><label class="form-label">Tournament</label><select class="form-select"><option>All tournaments</option>@foreach($tournaments as $tournament)<option>{{ $tournament->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">From</label><input type="date" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">To</label><input type="date" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Payment Method</label><select class="form-select"><option>All</option><option>Cash</option><option>Bkash</option><option>Bank</option></select></div>
        </div>
    </form>

    <div class="card"><div class="card-body">@include('admin.pages.finance.partials.empty-state', ['title' => 'No expenses recorded', 'message' => 'The expense UI is ready, but this checkout has no expense persistence model/table yet.', 'icon' => 'ri-receipt-line'])</div></div>
</div>

<div class="modal fade" id="expenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Expense</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Title</label><input class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Category</label><select class="form-select"><option>Tournament</option><option>Equipment</option><option>Ground</option><option>Other</option></select></div>
                <div class="col-md-4"><label class="form-label">Amount</label><input type="number" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Expense Date</label><input type="date" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Payment Method</label><select class="form-select"><option>Cash</option><option>Bkash</option><option>Bank</option></select></div>
                <div class="col-md-4"><label class="form-label">Voucher No</label><input class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Paid To</label><input class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Tournament</label><select class="form-select"><option>Optional</option>@foreach($tournaments as $tournament)<option>{{ $tournament->name }}</option>@endforeach</select></div>
                <div class="col-md-12"><label class="form-label">Attachment</label><input type="file" class="form-control"></div>
                <div class="col-md-12"><label class="form-label">Notes</label><textarea class="form-control" rows="3"></textarea></div>
            </div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button class="btn btn-danger" disabled>Save Expense</button></div>
    </div></div>
</div>
@endsection
