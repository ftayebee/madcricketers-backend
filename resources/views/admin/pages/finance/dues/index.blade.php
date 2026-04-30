@extends('admin.layouts.theme')

@push('styles')
    @include('admin.pages.finance.partials.styles')
@endpush

@section('content')
<div class="finance-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h3 class="mb-1">Player Dues</h3>
            <p class="text-muted mb-0">Track monthly dues and quickly collect full or partial payments.</p>
        </div>
        @can('finance-payments-manage')
            <a href="{{ route('admin.finance.payments.create') }}" class="btn btn-success"><i class="ri-hand-coin-line me-1"></i>Receive Payment</a>
        @endcan
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-header bg-white"><h5 class="mb-0">Bulk Due Assignment</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select class="form-select">
                        @foreach($categories as $key => $category)
                            <option value="{{ $key }}">{{ $category['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label">Amount</label><input class="form-control" type="number" placeholder="0.00"></div>
                <div class="col-md-3"><label class="form-label">Period Label</label><input class="form-control" placeholder="{{ now()->format('F Y') }}"></div>
                <div class="col-md-2"><label class="form-label">Due Date</label><input class="form-control" type="date"></div>
                <div class="col-md-2">
                    <label class="form-label">Tournament</label>
                    <select class="form-select"><option value="">Optional</option>@foreach($tournaments as $tournament)<option>{{ $tournament->name }}</option>@endforeach</select>
                </div>
                <div class="col-12 d-flex flex-wrap gap-3 align-items-center">
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="all_active" checked><label class="form-check-label" for="all_active">Assign to all active players</label></div>
                    <button type="button" class="btn btn-primary" disabled>Assign Due</button>
                    <span class="text-muted small">Bulk assignment needs the finance due store endpoint in backend.</span>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" class="finance-filter mb-3">
        <div class="row g-2">
            <input type="hidden" name="current_month_donation" value="{{ request('current_month_donation') }}">
            <div class="col-md-3"><label class="form-label">Player</label><select name="player_id" class="form-select"><option value="">All players</option>@foreach($players as $player)<option value="{{ $player->id }}" @selected(request('player_id') == $player->id)>{{ $player->user?->full_name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">Category</label><select name="category" class="form-select"><option value="">All</option>@foreach($categories as $key => $category)<option value="{{ $key }}">{{ $category['label'] }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">Status</label><select name="status" class="form-select"><option value="">All</option><option value="paid" @selected(request('status') === 'paid')>Paid</option><option value="partial" @selected(request('status') === 'partial')>Partial</option><option value="pending" @selected(request('status') === 'pending')>Pending</option><option value="overdue" @selected(request('status') === 'overdue')>Overdue</option><option value="waived">Waived</option></select></div>
            <div class="col-md-2"><label class="form-label">Date From</label><input name="start_date" type="date" class="form-control" value="{{ request('start_date') }}"></div>
            <div class="col-md-2"><label class="form-label">Date To</label><input name="end_date" type="date" class="form-control" value="{{ request('end_date') }}"></div>
            <div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary w-100"><i class="ri-filter-line"></i></button></div>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-3">
            <a href="{{ route('admin.finance.dues.index', ['current_month_donation' => 1]) }}" class="btn btn-sm {{ request('current_month_donation') ? 'btn-primary' : 'btn-outline-primary' }}">
                Current Month Donation
            </a>
            @if(request('current_month_donation'))
                <a href="{{ route('admin.finance.dues.index') }}" class="btn btn-sm btn-outline-secondary">Clear Current Month</a>
            @endif
        </div>
    </form>

    <div class="card">
        <div class="card-body">
            @if($dues->count())
                <div class="table-responsive">
                    <table class="table finance-table">
                        <thead><tr><th>Player</th><th>Category</th><th>Period</th><th>Amount</th><th>Paid</th><th>Remaining</th><th>Due Date</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            @foreach($dues as $due)
                                @php
                                    $remaining = $due->expected_amount - $due->paid_amount;
                                    $dueDate = \Carbon\Carbon::create($due->year, $due->month)->endOfMonth();
                                    $status = $due->is_paid ? 'paid' : ($due->paid_amount > 0 ? 'partial' : ($dueDate->isPast() ? 'overdue' : 'pending'));
                                @endphp
                                <tr>
                                    <td data-label="Player">{{ $due->player?->user?->full_name ?? '-' }}</td>
                                    <td data-label="Category">Monthly Donation</td>
                                    <td data-label="Period">{{ \Carbon\Carbon::create($due->year, $due->month)->format('F Y') }}</td>
                                    <td data-label="Amount">{{ number_format($due->expected_amount, 2) }}</td>
                                    <td data-label="Paid" class="text-success">{{ number_format($due->paid_amount, 2) }}</td>
                                    <td data-label="Remaining" class="text-danger fw-bold">{{ number_format($remaining, 2) }}</td>
                                    <td data-label="Due Date">{{ $dueDate->format('d M Y') }}</td>
                                    <td data-label="Status">@include('admin.pages.finance.partials.status-badge', ['status' => $status])</td>
                                    <td data-label="Actions">
                                        <div class="finance-action">
                                            @can('finance-payments-manage')
                                                <a class="btn btn-sm btn-success" href="{{ route('admin.finance.payments.create', ['due_id' => $due->id]) }}">Collect</a>
                                            @endcan
                                            <button class="btn btn-sm btn-outline-warning" disabled>Waive</button>
                                            <button class="btn btn-sm btn-outline-primary" disabled>Edit</button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this due?')" disabled>Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $dues->links() }}
            @else
                @include('admin.pages.finance.partials.empty-state', ['title' => 'No dues found', 'message' => 'Adjust filters or assign dues when backend assignment is available.'])
            @endif
        </div>
    </div>
</div>
@endsection
