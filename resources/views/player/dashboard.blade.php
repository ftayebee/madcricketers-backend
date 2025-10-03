@extends('player.layouts.theme')

@section('content')
    <div class="row g-4">

        {{-- Player Profile --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if ($player->user->profile_photo)
                            <img src="{{ asset('storage/uploads/players/' . $player->user->profile_photo) }}"
                                class="rounded-circle img-thumbnail" style="width: 120px; height: 120px;" alt="Profile">
                        @else
                            <i class="ri-user-3-line text-muted display-3"></i>
                        @endif
                    </div>
                    <h4 class="fw-bold">{{ $player->user->full_name }}</h4>
                    <p class="text-muted mb-1">Team: {{ $player->team->name ?? 'Unassigned' }}</p>
                    <p class="text-muted">Role: {{ ucfirst($player->role ?? 'All-Rounder') }}</p>
                </div>
            </div>
        </div>

        {{-- Player Statistics --}}
        <div class="col-md-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-light fw-bold">Your Overall Stats</div>
                <div class="card-body">
                    <div class="row g-3 text-center">

                        {{-- Batting Stats --}}
                        <div class="col-6 col-md-2">
                            <h5 class="fw-bold">{{ $stats['matches'] ?? 0 }}</h5>
                            <p class="text-muted mb-0">Matches</p>
                        </div>
                        <div class="col-6 col-md-2">
                            <h5 class="fw-bold">{{ $stats['innings_batted'] ?? 0 }}</h5>
                            <p class="text-muted mb-0">Innings Batted</p>
                        </div>
                        <div class="col-6 col-md-2">
                            <h5 class="fw-bold">{{ $stats['total_runs'] ?? 0 }}</h5>
                            <p class="text-muted mb-0">Runs</p>
                        </div>
                        <div class="col-6 col-md-2">
                            <h5 class="fw-bold">{{ $stats['balls_faced'] ?? 0 }}</h5>
                            <p class="text-muted mb-0">Balls Faced</p>
                        </div>
                        <div class="col-6 col-md-2">
                            <h5 class="fw-bold">{{ $stats['fours'] ?? 0 }}</h5>
                            <p class="text-muted mb-0">Fours</p>
                        </div>
                        <div class="col-6 col-md-2">
                            <h5 class="fw-bold">{{ $stats['sixes'] ?? 0 }}</h5>
                            <p class="text-muted mb-0">Sixes</p>
                        </div>

                        {{-- Batting Derived Stats --}}
                        <div class="col-6 col-md-2">
                            <h5 class="fw-bold">{{ $stats['strike_rate'] ?? 0 }}</h5>
                            <p class="text-muted mb-0">Strike Rate</p>
                        </div>
                        <div class="col-6 col-md-2">
                            <h5 class="fw-bold">{{ $stats['average'] ?? 0 }}</h5>
                            <p class="text-muted mb-0">Average</p>
                        </div>

                        {{-- Bowling Stats --}}
                        <div class="col-6 col-md-2">
                            <h5 class="fw-bold">{{ $stats['innings_bowled'] ?? 0 }}</h5>
                            <p class="text-muted mb-0">Innings Bowled</p>
                        </div>
                        <div class="col-6 col-md-2">
                            <h5 class="fw-bold">{{ $stats['overs_bowled'] ?? 0 }}</h5>
                            <p class="text-muted mb-0">Overs</p>
                        </div>
                        <div class="col-6 col-md-2">
                            <h5 class="fw-bold">{{ $stats['runs_conceded'] ?? 0 }}</h5>
                            <p class="text-muted mb-0">Runs Conceded</p>
                        </div>
                        <div class="col-6 col-md-2">
                            <h5 class="fw-bold">{{ $stats['wickets'] ?? 0 }}</h5>
                            <p class="text-muted mb-0">Wickets</p>
                        </div>
                        <div class="col-6 col-md-2">
                            <h5 class="fw-bold">{{ $stats['bowling_average'] ?? 0 }}</h5>
                            <p class="text-muted mb-0">Bowling Avg</p>
                        </div>
                        <div class="col-6 col-md-2">
                            <h5 class="fw-bold">{{ $stats['economy_rate'] ?? 0 }}</h5>
                            <p class="text-muted mb-0">Economy</p>
                        </div>

                        {{-- Fielding Stats --}}
                        <div class="col-6 col-md-2">
                            <h5 class="fw-bold">{{ $stats['catches'] ?? 0 }}</h5>
                            <p class="text-muted mb-0">Catches</p>
                        </div>
                        <div class="col-6 col-md-2">
                            <h5 class="fw-bold">{{ $stats['runouts'] ?? 0 }}</h5>
                            <p class="text-muted mb-0">Run Outs</p>
                        </div>
                        <div class="col-6 col-md-2">
                            <h5 class="fw-bold">{{ $stats['stumpings'] ?? 0 }}</h5>
                            <p class="text-muted mb-0">Stumpings</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>


        {{-- Recent Matches --}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-light fw-bold">Recent Matches</div>
                <div class="card-body">
                    @if ($recentMatches->count())
                        <ul class="list-group list-group-flush">
                            @foreach ($recentMatches as $match)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $match->title }}</span>
                                    <span class="badge bg-primary">{{ ucfirst($match->status) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No matches played recently.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Upcoming Matches --}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-light fw-bold">Upcoming Matches</div>
                <div class="card-body">
                    @if ($upcomingMatches->count())
                        <ul class="list-group list-group-flush">
                            @foreach ($upcomingMatches as $match)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $match->title }}</span>
                                    <small
                                        class="text-muted">{{ \Carbon\Carbon::parse($match->match_date)->format('d M Y, h:i A') }}</small>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No upcoming matches scheduled.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Payment Summary --}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-light fw-bold">Payment Summary (This Month)</div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between">
                            Donation Fees <span class="fw-bold">{{ $paymentSummary['donation'] ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            Tournament Fees <span class="fw-bold">{{ $paymentSummary['tournament'] ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            Jersey Fees <span class="fw-bold">{{ $paymentSummary['jersey'] ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            Other Fees <span class="fw-bold">{{ $paymentSummary['other'] ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between bg-light">
                            <strong>Total Paid</strong> <strong>{{ $paymentSummary['total'] ?? 0 }}</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Notifications --}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-light fw-bold">Notifications</div>
                <div class="card-body">
                    @if ($notifications->count())
                        <ul class="list-group list-group-flush">
                            @foreach ($notifications as $note)
                                <li class="list-group-item">
                                    <i class="ri-notification-3-line text-primary me-2"></i>
                                    {{ $note->message }}
                                    <small class="text-muted d-block">{{ $note->created_at->diffForHumans() }}</small>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No new notifications.</p>
                    @endif
                </div>
            </div>
        </div>

    </div>
@endsection
