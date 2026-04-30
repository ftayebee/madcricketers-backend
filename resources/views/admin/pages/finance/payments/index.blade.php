@extends('admin.layouts.theme')

@push('styles')
    @include('admin.pages.finance.partials.styles')
@endpush

@section('content')
<div class="finance-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div><h3 class="mb-1">Payment History</h3><p class="text-muted mb-0">Search and review received player payments.</p></div>
        @can('finance-payments-manage')
            <a href="{{ route('admin.finance.payments.create') }}" class="btn btn-success"><i class="ri-hand-coin-line me-1"></i>Receive Payment</a>
        @endcan
    </div>

    <form method="GET" class="finance-filter mb-3">
        <div class="row g-2">
            <div class="col-md-3"><label class="form-label">Player</label><select name="player_id" class="form-select"><option value="">All players</option>@foreach($players as $player)<option value="{{ $player->id }}" @selected(request('player_id') == $player->id)>{{ $player->user?->full_name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">Category</label><select name="category" class="form-select"><option value="">All</option>@foreach($categories as $key => $category)<option value="{{ $key }}" @selected(request('category') === $key)>{{ $category['label'] }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">Method</label><select name="method" class="form-select"><option value="">All</option><option>Cash</option><option>Bkash</option><option>Bank</option></select></div>
            <div class="col-md-2"><label class="form-label">From</label><input name="start_date" type="date" class="form-control" value="{{ request('start_date') }}"></div>
            <div class="col-md-2"><label class="form-label">To</label><input name="end_date" type="date" class="form-control" value="{{ request('end_date') }}"></div>
            <div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary w-100"><i class="ri-search-line"></i></button></div>
        </div>
    </form>

    <div class="card">
        <div class="card-body">
            @if($payments->count())
                <div class="table-responsive">
                    <table class="table finance-table">
                        <thead><tr><th>Player</th><th>Category</th><th>Due</th><th>Received</th><th>Date</th><th>Method</th><th>Received By</th><th>Action</th></tr></thead>
                        <tbody>
                            @foreach($payments as $payment)
                                <tr>
                                    <td data-label="Player">{{ $payment->player?->user?->full_name ?? '-' }}</td>
                                    <td data-label="Category"><span class="badge bg-info">{{ ucfirst($payment->type) }}</span></td>
                                    <td data-label="Due">{{ $payment->tournament?->name ?? 'General' }}</td>
                                    <td data-label="Received" class="text-success fw-bold">{{ number_format($payment->amount, 2) }}</td>
                                    <td data-label="Date">{{ optional($payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date) : null)->format('d M Y') ?? '-' }}</td>
                                    <td data-label="Method">-</td>
                                    <td data-label="Received By">{{ auth()->user()?->full_name ?? 'Admin' }}</td>
                                    <td data-label="Action"><button class="btn btn-sm btn-outline-danger" disabled onclick="return confirm('Reverse this payment?')">Reverse</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $payments->links() }}
            @else
                @include('admin.pages.finance.partials.empty-state', ['title' => 'No payments found', 'message' => 'Try changing filters or receive a new payment.'])
            @endif
        </div>
    </div>
</div>
@endsection
