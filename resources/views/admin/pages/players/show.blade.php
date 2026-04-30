@extends('admin.layouts.theme')

@section('content')

    @push('styles')
        <style>
            .custom-icon {
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 1.5rem;
                /* matches other ri icons size */
                width: 60px;
                height: 60px;
                border-radius: 12px;
                background-color: rgba(13, 110, 253, 0.1);
                /* light primary */
                color: #0d6efd;
                /* primary color */
                text-align: center;
            }

            /* Custom number icons */
            .ri-4-line::before {
                content: "4";
            }

            .ri-6-line::before {
                content: "6";
            }

            .user-profile-card {
                border: 1px solid #eaeaea;
            }

            .approve-btn:hover {
                transform: scale(1.1);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            }

            .profile-header {
                border-bottom: 1px solid #f0f0f0;
                padding-bottom: 1rem;
            }

            .detail-item {
                background: #f9f9f9;
                padding: 0.75rem;
                border-radius: 5px;
            }

            .additional-info h6 {
                font-size: 0.8rem;
            }

            .avatar-placeholder {
                border: 1px dashed #ddd;
            }

            .finance-mini-card {
                border: 1px solid #e8eef5;
                border-radius: 8px;
                padding: 1rem;
                background: #fff;
                height: 100%;
            }
        </style>
    @endpush
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="card shadow custom-card-border">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Player Profile</h4>
                    <div>
                        <a href="{{ route('admin.players.index') }}" class="btn btn-light">← Back to List</a>
                        <a href="{{ route('admin.settings.users.edit', $user->id) }}" class="btn btn-light">Edit</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="user-profile-card bg-white p-4 rounded shadow-sm">
                        <!-- Header Section -->
                        <div class="profile-header d-flex align-items-center mb-4">
                            <div class="d-flex align-items-center justify-content-between">
                                @if ($user->image)
                                    <img src="{{ $user->image }}" alt="{{ $user->full_name }}" class="rounded-circle me-3"
                                        width="80" height="80">
                                @else
                                    <div class="avatar-placeholder rounded-circle me-3 d-flex align-items-center justify-content-center"
                                        style="width: 80px; height: 80px; background: #f0f0f0;">
                                        <i class="fa fa-user fa-2x text-muted"></i>
                                    </div>
                                @endif
                                <div>
                                    <h3 class="mb-1">{{ $user->full_name ?? 'Unnamed Player' }}</h3>
                                    <span
                                        class="badge bg-primary fs-6">{{ strtoupper($user->player?->player_type ?? 'Player') }}</span>
                                    @if ($user->team)
                                        <span class="badge bg-secondary ms-1">{{ $user->team->name }}</span>
                                    @endif
                                    <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'danger' }} fs-6">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ms-auto">
                                <p class="mb-1 text-muted">Mark Player Application As</p>
                                <form action="{{ route('admin.players.approve', $user->id) }}" method="POST"
                                    class="form-type-update" data-id="{{ $user->id }}">
                                    @csrf
                                    <div class="form-check form-switch">
                                        <input class="form-check-input approve-switch" type="checkbox"
                                            id="approveSwitch{{ $user->id }}" name="approve"
                                            {{ $user->player && $user->player->player_type === 'registered' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="approveSwitch{{ $user->id }}">
                                            {{ ($user->player?->player_type === 'registered') ? 'Registered' : 'Guest' }}
                                        </label>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Player Details Grid -->
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-3">
                                <div class="detail-item mb-3">
                                    <h6 class="text-muted mb-1 fs-5">Player Role</h6>
                                    <p class="mb-0">
                                        {{ ucwords(str_replace('-', ' ', $user->player->player_role ?? 'N/A')) }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="detail-item mb-0">
                                    <h6 class="text-muted mb-1 fs-5">Batting Style</h6>
                                    <p class="mb-0">
                                        {{ ucfirst($user->player->batting_style ?? 'N/A') }}
                                        @if ($user->player->batting_style)
                                            <i
                                                class="fa fa-{{ $user->player->batting_style == 'left-handed' ? 'hand-o-left' : 'hand-o-right' }} ms-2"></i>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-3">
                                <div class="detail-item mb-3">
                                    <h6 class="text-muted mb-1 fs-5">Bowling Style</h6>
                                    <p class="mb-0">{{ ucfirst($user->player->bowling_style ?? 'N/A') }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="detail-item mb-0">
                                    <h6 class="text-muted mb-1 fs-5">Player Since</h6>
                                    <p class="mb-0">{{ $user->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Info -->
                        <div class="additional-info mt-3 pt-3 border-top">
                            @php
                                $player = $user->player;
                                // Aggregate player stats from match_players
                                $matchesCount = $player->matches ? $player->matches->count() : 0;
                                $totalRuns = $player->matches
                                    ? $player->matches->sum(
                                        fn($m) => $m->players->where('player_id', $player->id)->first()->runs_scored ??
                                            0,
                                    )
                                    : 0;
                                $totalBalls = $player->matches
                                    ? $player->matches->sum(
                                        fn($m) => $m->players->where('player_id', $player->id)->first()->balls_faced ??
                                            0,
                                    )
                                    : 0;
                                $totalFours = $player->matches
                                    ? $player->matches->sum(
                                        fn($m) => $m->players->where('player_id', $player->id)->first()->fours ?? 0,
                                    )
                                    : 0;
                                $totalSixes = $player->matches
                                    ? $player->matches->sum(
                                        fn($m) => $m->players->where('player_id', $player->id)->first()->sixes ?? 0,
                                    )
                                    : 0;
                                $totalWickets = $player->matches
                                    ? $player->matches->sum(
                                        fn($m) => $m->players->where('player_id', $player->id)->first()
                                            ->wickets_taken ?? 0,
                                    )
                                    : 0;
                                $totalOvers = $player->matches
                                    ? $player->matches->sum(
                                        fn($m) => $m->players->where('player_id', $player->id)->first()->overs_bowled ??
                                            0,
                                    )
                                    : 0;
                                $totalRunsConceded = $player->matches
                                    ? $player->matches->sum(
                                        fn($m) => $m->players->where('player_id', $player->id)->first()
                                            ->runs_conceded ?? 0,
                                    )
                                    : 0;
                                $highestScore = $player->matches
                                    ? $player->matches
                                        ->map(
                                            fn($m) => $m->players->where('player_id', $player->id)->first()
                                                ->runs_scored ?? 0,
                                        )
                                        ->max()
                                    : 0;
                                $strikeRate = $totalBalls > 0 ? round(($totalRuns / $totalBalls) * 100, 2) : 0;
                                $economyRate = $totalOvers > 0 ? round($totalRunsConceded / $totalOvers, 2) : 0;
                            @endphp

                            <div class="row g-3 mt-0">
                                @php
                                    $stats = [
                                        [
                                            'label' => 'Matches',
                                            'value' => $matchesCount,
                                            'icon' => 'fa-solid fa-chess-board',
                                        ],
                                        ['label' => 'Runs', 'value' => $totalRuns, 'icon' => 'ri-run-line'],
                                        [
                                            'label' => 'Balls Faced',
                                            'value' => $totalBalls,
                                            'icon' => 'ri-checkbox-blank-circle-line',
                                        ],
                                        [
                                            'label' => 'Highest Score',
                                            'value' => $highestScore,
                                            'icon' => 'ri-star-line',
                                        ],
                                        ['label' => 'Fours', 'value' => $totalFours, 'icon' => 'ri-4-line'],
                                        ['label' => 'Sixes', 'value' => $totalSixes, 'icon' => 'ri-6-line'],
                                        
                                        ['label' => 'Wickets', 'value' => $totalWickets, 'icon' => 'ri-cricket-line'],
                                        ['label' => 'Overs Bowled', 'value' => $totalOvers, 'icon' => 'ri-time-line'],
                                        [
                                            'label' => 'Runs Conceded',
                                            'value' => $totalRunsConceded,
                                            'icon' => 'ri-bar-chart-line',
                                        ],
                                        ['label' => 'Strike Rate', 'value' => $strikeRate, 'icon' => 'ri-speed-line'],
                                        [
                                            'label' => 'Economy Rate',
                                            'value' => $economyRate,
                                            'icon' => 'ri-speed-mini-line',
                                        ],
                                    ];
                                @endphp
                                @foreach ($stats as $stat)
                                    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                                        <div class="card shadow-sm border-0 h-100">
                                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                                <!-- Icon box -->
                                                <div class="bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center rounded p-3 fs-3"
                                                    style="width:60px; height:60px;">
                                                    <i class="{{ $stat['icon'] }}"></i>
                                                </div>

                                                <!-- Stat details -->
                                                <div class="flex-grow-1">
                                                    <h5 class="text-muted mb-1">{{ $stat['label'] }}</h5>
                                                    <h4 class="fw-bold mb-0">{{ $stat['value'] }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3 mb-2 mb-sm-0">
                            <div class="nav flex-column nav-pills" id="vl-pills-tab" role="tablist"
                                aria-orientation="vertical">
                                <a class="nav-link" id="vl-pills-home-tab" data-bs-toggle="pill"
                                    href="#vl-pills-home" role="tab" aria-controls="vl-pills-home" aria-selected="false">
                                    <span>About Player</span>
                                </a>
                                <a class="nav-link active show" id="vl-pills-profile-tab" data-bs-toggle="pill" href="#vl-pills-profile"
                                    role="tab" aria-controls="vl-pills-profile" aria-selected="true">
                                    <span>Statistics</span>
                                </a>
                                @canany(['finance-view', 'finance-dues-manage', 'finance-payments-manage', 'finance-reports-view'])
                                    <a class="nav-link" id="vl-pills-finance-tab" data-bs-toggle="pill" href="#vl-pills-finance"
                                        role="tab" aria-controls="vl-pills-finance" aria-selected="false">
                                        <span>Finance</span>
                                    </a>
                                @endcanany
                            </div>
                        </div>

                        <div class="col-sm-9">
                            <div class="tab-content pt-0" id="vl-pills-tabContent">
                                <div class="tab-pane fade" id="vl-pills-home" role="tabpanel"
                                    aria-labelledby="vl-pills-home-tab">
                                    <p class="mb-0">{{ $user->bio ?? 'No biography available.' }}</p>
                                </div>
                                <div class="tab-pane fade active show" id="vl-pills-profile" role="tabpanel"
                                    aria-labelledby="vl-pills-profile-tab">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Match</th>
                                                    <th class="text-center">Runs Scored</th>
                                                    <th class="text-center">Balls Faced</th>
                                                    <th class="text-center">Fours</th>
                                                    <th class="text-center">Sixes</th>
                                                    <th class="text-center">Wickets Taken</th>
                                                    <th class="text-center">Overs Bowled</th>
                                                    <th class="text-center">Runs Conceded</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $player = $user->player;
                                                @endphp

                                                @if ($player && $player->matches && $player->matches->count() > 0)
                                                    @foreach ($player->matches as $match)
                                                        @php
                                                            // Get MatchPlayer record for this player and match
                                                            $stats = $match
                                                                ->players()
                                                                ->where('player_id', $player->id)
                                                                ->first();
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $match->title }}</td>
                                                            <td class="text-center">{{ $stats->runs_scored ?? 0 }}</td>
                                                            <td class="text-center">{{ $stats->balls_faced ?? 0 }}</td>
                                                            <td class="text-center">{{ $stats->fours ?? 0 }}</td>
                                                            <td class="text-center">{{ $stats->sixes ?? 0 }}</td>
                                                            <td class="text-center">{{ $stats->wickets_taken ?? 0 }}</td>
                                                            <td class="text-center">{{ $stats->overs_bowled ?? 0 }}</td>
                                                            <td class="text-center">{{ $stats->runs_conceded ?? 0 }}</td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="8" class="text-center">No matches played yet.</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                                @canany(['finance-view', 'finance-dues-manage', 'finance-payments-manage', 'finance-reports-view'])
                                <div class="tab-pane fade" id="vl-pills-finance" role="tabpanel"
                                    aria-labelledby="vl-pills-finance-tab">
                                    @php
                                        $financePlayer = $user->player;
                                        $playerPayments = $financePlayer ? $financePlayer->payments()->latest('payment_date')->take(8)->get() : collect();
                                        $playerDues = $financePlayer ? $financePlayer->monthlyDonations()->latest()->take(8)->get() : collect();
                                        $totalPaid = $playerPayments->where('status', 'paid')->sum('amount');
                                        $totalDue = $playerDues->sum('expected_amount');
                                        $paidAgainstDues = $playerDues->sum('paid_amount');
                                        $remainingBalance = max(0, $totalDue - $paidAgainstDues);
                                        $overdueAmount = $playerDues->filter(fn($due) => !$due->is_paid && \Carbon\Carbon::create($due->year, $due->month)->endOfMonth()->isPast())->sum(fn($due) => $due->expected_amount - $due->paid_amount);
                                    @endphp

                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                        <h5 class="mb-0">Player Finance</h5>
                                        @if($financePlayer)
                                            <div class="d-flex flex-wrap gap-2">
                                                @can('finance-dues-manage')
                                                    <a href="{{ route('admin.finance.dues.index', ['player_id' => $financePlayer->id]) }}" class="btn btn-sm btn-primary">Add due for this player</a>
                                                @endcan
                                                @can('finance-payments-manage')
                                                    <a href="{{ route('admin.finance.payments.create', ['player_id' => $financePlayer->id]) }}" class="btn btn-sm btn-success">Receive payment</a>
                                                @endcan
                                            </div>
                                        @endif
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-3"><div class="finance-mini-card"><p class="text-muted mb-1">Total Paid</p><h4 class="text-success mb-0">{{ number_format($totalPaid, 2) }}</h4></div></div>
                                        <div class="col-md-3"><div class="finance-mini-card"><p class="text-muted mb-1">Total Due</p><h4 class="text-primary mb-0">{{ number_format($totalDue, 2) }}</h4></div></div>
                                        <div class="col-md-3"><div class="finance-mini-card"><p class="text-muted mb-1">Remaining Balance</p><h4 class="text-warning mb-0">{{ number_format($remainingBalance, 2) }}</h4></div></div>
                                        <div class="col-md-3"><div class="finance-mini-card"><p class="text-muted mb-1">Overdue Amount</p><h4 class="text-danger mb-0">{{ number_format($overdueAmount, 2) }}</h4></div></div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-lg-6">
                                            <div class="card border">
                                                <div class="card-header bg-white fw-bold">Dues</div>
                                                <div class="card-body">
                                                    @if($playerDues->count())
                                                        <div class="table-responsive">
                                                            <table class="table table-sm">
                                                                <thead><tr><th>Period</th><th>Amount</th><th>Paid</th><th>Status</th></tr></thead>
                                                                <tbody>
                                                                    @foreach($playerDues as $due)
                                                                        @php $status = $due->is_paid ? 'Paid' : ($due->paid_amount > 0 ? 'Partial' : 'Pending'); @endphp
                                                                        <tr><td>{{ \Carbon\Carbon::create($due->year, $due->month)->format('F Y') }}</td><td>{{ number_format($due->expected_amount, 2) }}</td><td>{{ number_format($due->paid_amount, 2) }}</td><td><span class="badge bg-{{ $due->is_paid ? 'success' : 'warning text-dark' }}">{{ $status }}</span></td></tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <p class="text-muted mb-0">No dues recorded for this player.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="card border">
                                                <div class="card-header bg-white fw-bold">Recent Payments</div>
                                                <div class="card-body">
                                                    @if($playerPayments->count())
                                                        <div class="table-responsive">
                                                            <table class="table table-sm">
                                                                <thead><tr><th>Date</th><th>Category</th><th>Amount</th><th>Status</th></tr></thead>
                                                                <tbody>
                                                                    @foreach($playerPayments as $payment)
                                                                        <tr><td>{{ optional($payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date) : null)->format('d M Y') ?? '-' }}</td><td>{{ ucfirst($payment->type) }}</td><td class="text-success">{{ number_format($payment->amount, 2) }}</td><td><span class="badge bg-{{ $payment->status === 'paid' ? 'success' : 'warning text-dark' }}">{{ ucfirst($payment->status ?? 'pending') }}</span></td></tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <p class="text-muted mb-0">No payments recorded for this player.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endcanany
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.approve-switch').forEach((checkbox) => {
            checkbox.addEventListener('change', function() {
                const form = this.closest('.form-type-update');
                const userId = form.dataset.id;
                const redirectTo = "{{ url()->current() }}"
                const approved = this.checked ? 'on' : 'off'; // mimic what backend expects

                fetch(`/admin/players/approve/${userId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({
                            approve: approved,
                            redirection: redirectTo
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                window.location.reload();
                            }
                        } else {
                            alert('Failed to update player status.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Error updating status.');
                    });
            });
        });
    </script>
@endpush
