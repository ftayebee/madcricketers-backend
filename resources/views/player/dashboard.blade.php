@extends('player.layouts.theme')

@section('content')
    @push('styles')
        <style>
            .stat-card {
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                overflow: hidden;
                position: relative;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                border: 1px solid #bbbaff;
            }

            .stat-card:hover {
                transform: translateY(-8px) scale(1.05);
                box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
            }

            .stat-number {
                font-size: 1.5rem;
                font-weight: 700;
                color: #333;
            }

            .stat-label {
                font-size: 0.85rem;
                font-weight: 500;
                color: #777;
                margin-top: 4px;
            }

            /* Soft background accent */
            .stat-bg {
                position: absolute;
                top: -20%;
                right: -20%;
                width: 80px;
                height: 80px;
                background: rgba(0, 123, 255, 0.1);
                border-radius: 50%;
                z-index: 0;
            }

            /* Ensure numbers and label are on top */
            .stat-number,
            .stat-label {
                position: relative;
                z-index: 1;
            }
        </style>
    @endpush
    <div class="row g-4">

        {{-- Player Statistics --}}
        <div class="col-md-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-light fw-bold fs-20 text-center">Your Overall Stats</div>
                <div class="card-body">
                    <div class="row g-3 text-center">

                        @php
                            $allStats = [
                                'Matches' => 'matches',
                                'Innings Batted' => 'innings_batted',
                                'Runs' => 'total_runs',
                                'Balls Faced' => 'balls_faced',
                                'Fours' => 'fours',
                                'Sixes' => 'sixes',
                                'Strike Rate' => 'strike_rate',
                                'Average' => 'average',
                                'Innings Bowled' => 'innings_bowled',
                                'Overs' => 'overs_bowled',
                                'Runs Conceded' => 'runs_conceded',
                                'Wickets' => 'wickets',
                                'Bowling Avg' => 'bowling_average',
                                'Economy' => 'economy_rate',
                                'Catches' => 'catches',
                                'Run Outs' => 'runouts',
                                'Stumpings' => 'stumpings',
                            ];
                        @endphp

                        @foreach ($allStats as $label => $key)
                            <div class="col-6 col-md-2">
                                <div class="stat-card p-3 rounded shadow-sm position-relative">
                                    <div class="stat-number">{{ $stats[$key] ?? 0 }}</div>
                                    <div class="stat-label">{{ $label }}</div>
                                    <div class="stat-bg"></div>
                                </div>
                            </div>
                        @endforeach
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
