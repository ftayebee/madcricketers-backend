@extends('admin.layouts.theme')

@push('styles')
    @include('admin.pages.finance.partials.styles')
@endpush

@section('content')
@php
    $selectedPlayerId = old('player_id', $selectedDue?->player_id ?? request('player_id'));
    $selectedAmount = old('amount', $selectedDue ? max(0, $selectedDue->expected_amount - $selectedDue->paid_amount) : null);
@endphp
<div class="finance-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div><h3 class="mb-1">Receive Payment</h3><p class="text-muted mb-0">Collect full or partial player payments with clear due visibility.</p></div>
        <a href="{{ route('admin.finance.payments.index') }}" class="btn btn-outline-primary"><i class="ri-history-line me-1"></i>Payment History</a>
    </div>

    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-white"><h5 class="mb-0">Payment Details</h5></div>
                <div class="card-body">
                    <form action="{{ route('admin.payments.store') }}" method="POST" id="finance-payment-form">
                        @csrf
                        <input type="hidden" name="due_id" value="{{ $selectedDue?->id }}">
                        <div class="mb-3">
                            <label class="form-label">Player</label>
                            <select name="player_id" id="finance-player" class="form-select" required>
                                <option value="">Select player</option>
                                @foreach($players as $player)
                                    <option value="{{ $player->id }}" @selected($selectedPlayerId == $player->id)>{{ $player->user?->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Due</label>
                            <select id="finance-due" name="due_select" class="form-select">
                                <option value="">General payment</option>
                                @foreach($players as $player)
                                    @foreach($player->monthlyDonations as $due)
                                        @php $remaining = $due->expected_amount - $due->paid_amount; @endphp
                                        <option data-player="{{ $player->id }}" data-remaining="{{ $remaining }}" value="{{ $due->id }}" @selected($selectedDue?->id === $due->id)>{{ \Carbon\Carbon::create($due->year, $due->month)->format('F Y') }} - Remaining {{ number_format($remaining, 2) }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">Payment Amount</label><input id="finance-amount" name="amount" type="number" step="0.01" min="0" class="form-control" required value="{{ $selectedAmount }}"></div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="form-label">Category</label><select name="type" class="form-select">@foreach($categories as $key => $category)<option value="{{ $key }}" @selected($key === 'donation')>{{ $category['label'] }}</option>@endforeach</select></div>
                            <div class="col-md-6"><label class="form-label">Status</label><select name="status" class="form-select"><option value="paid">Paid</option><option value="pending">Pending</option></select></div>
                        </div>
                        <div class="row g-2 mt-1">
                            <div class="col-md-6"><label class="form-label">Payment Method</label><select class="form-select"><option>Cash</option><option>Bkash</option><option>Bank</option><option>Other</option></select></div>
                            <div class="col-md-6"><label class="form-label">Payment Date</label><input name="payment_date" type="date" class="form-control" value="{{ old('payment_date', now()->toDateString()) }}" required></div>
                        </div>
                        <div class="mb-3 mt-3"><label class="form-label">Transaction/Reference</label><input name="reference" class="form-control" value="{{ old('reference') }}" placeholder="Receipt, mobile transaction, or note"></div>
                        <div class="mb-3"><label class="form-label">Notes</label><textarea class="form-control" rows="3" placeholder="Internal note"></textarea></div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" id="pay-full-due" class="btn btn-outline-success"><i class="ri-check-double-line me-1"></i>Pay Full Due</button>
                            <button type="submit" class="btn btn-success"><i class="ri-save-3-line me-1"></i>Save Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-white"><h5 class="mb-0">Player Existing Dues</h5></div>
                <div class="card-body" id="player-dues-panel">@include('admin.pages.finance.partials.empty-state', ['title' => 'Select a player', 'message' => 'Unpaid and partial dues will appear here.'])</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const dues = @json($players->mapWithKeys(fn($player) => [$player->id => $player->monthlyDonations->map(fn($due) => [
        'id' => $due->id,
        'period' => \Carbon\Carbon::create($due->year, $due->month)->format('F Y'),
        'amount' => (float) $due->expected_amount,
        'paid' => (float) $due->paid_amount,
        'remaining' => (float) ($due->expected_amount - $due->paid_amount),
    ])->values()]));

    function renderDues(playerId) {
        const rows = dues[playerId] || [];
        const panel = $('#player-dues-panel');
        $('#finance-due option').each(function () {
            const visible = !$(this).data('player') || String($(this).data('player')) === String(playerId);
            $(this).toggle(visible);
        });
        if (!rows.length) {
            panel.html(`<div class="finance-empty"><i class="ri-shield-check-line fs-1 d-block mb-2"></i><h5 class="mb-1">No open dues</h5><p class="mb-0">This player has no unpaid monthly dues.</p></div>`);
            return;
        }
        panel.html(rows.map(row => `<div class="d-flex flex-wrap justify-content-between align-items-center border rounded p-3 mb-2">
            <div><strong>${row.period}</strong><div class="small text-muted">Paid ${row.paid.toFixed(2)} of ${row.amount.toFixed(2)}</div></div>
            <div class="d-flex align-items-center gap-2"><span class="badge bg-danger">${row.remaining.toFixed(2)}</span><button type="button" class="btn btn-sm btn-success choose-due" data-id="${row.id}" data-remaining="${row.remaining}">Collect</button></div>
        </div>`).join(''));
    }

    $('#finance-player').on('change', function () { renderDues(this.value); });
    $(document).on('click', '.choose-due', function () {
        $('#finance-due').val($(this).data('id'));
        $('input[name="due_id"]').val($(this).data('id'));
        $('#finance-amount').val($(this).data('remaining'));
    });
    $('#finance-due').on('change', function () {
        const selected = $(this).find(':selected');
        $('input[name="due_id"]').val(this.value);
        if (selected.data('remaining')) $('#finance-amount').val(selected.data('remaining'));
    });
    $('#pay-full-due').on('click', function () {
        const selected = $('#finance-due').find(':selected');
        if (selected.data('remaining')) $('#finance-amount').val(selected.data('remaining'));
    });
    renderDues($('#finance-player').val());
</script>
@endpush
