@extends('admin.layouts.theme')

@section('content')
    <div class="row g-4">
        {{-- Current Tournament --}}
        <div class="col-md-12">
            <div class="card shadow-sm rounded-2 custom-card-border">
                <div class="card-header bg-light fw-bold">Current Tournament</div>
                <div class="card-body">
                    @if ($currentTournament)
                        <h5 class="fw-bold">{{ $currentTournament->name }}</h5>
                        <p>Status: <span class="badge bg-primary">{{ ucfirst($currentTournament->status) }}</span></p>
                        <p>Teams: {{ $currentTournament->groups->flatMap->teams->unique('id')->count() }}</p>
                        <p>Matches: {{ $currentTournament->matches()->count() }}</p>
                    @else
                        <p class="text-muted">No tournament is running currently.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Payment Summary --}}
        <div class="col-md-6">
            <div class="card shadow-sm rounded-2 custom-card-border">
                <div class="card-header bg-light fw-bold">Payments (This Month)</div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between">
                            Donations <span class="fw-bold">{{ $paymentSummary['donations'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            Tournament Fees <span class="fw-bold">{{ $paymentSummary['tournament'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            Jersey Fees <span class="fw-bold">{{ $paymentSummary['jersey'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            Other Fees <span class="fw-bold">{{ $paymentSummary['other'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between bg-light">
                            <strong>Total</strong> <strong>{{ $paymentSummary['total'] }}</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Players Not Paid --}}
        <div class="col-md-6">
            <div class="card shadow-sm rounded-2 custom-card-border">
                <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
                    <span>Players Not Paid This Month</span>
                    @can('finance-dues-manage')
                    @if ($playersNotPaid->count() > 5)
                        <a href="{{ route('admin.finance.dues.index') }}" class="btn btn-sm btn-outline-primary">
                            View More
                        </a>
                    @endif
                    @endcan
                </div>
                <div class="card-body">
                    @if ($playersNotPaid->count())
                        <ul class="list-group">
                            @foreach ($playersNotPaid->take(5) as $player)
                                <li class="list-group-item">{{ $player->user->full_name }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">All players have paid this month ✅</p>
                    @endif
                </div>
            </div>
        </div>

    </div>
@endsection
