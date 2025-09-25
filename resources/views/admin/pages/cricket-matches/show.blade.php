@extends('admin.layouts.theme')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link href="{{ asset('storage/backend/css/scoreboard.css') }}" rel="stylesheet">
    <style>
        .tab-pane {
            display: none !important;
        }

        .tab-pane.show.active {
            display: block !important;
        }

        .section-title {
            font-weight: 700;
            font-size: 1.25rem;
            background: linear-gradient(90deg, #1B3C53 0%, #31326F 100%);
            color: #fff;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-transform: uppercase;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            letter-spacing: 1px;
            border-left: 5px solid #ffcc00;
        }

        @media (max-width: 576px) {
            .section-title {
                font-size: 1rem;
                padding: 10px 15px;
            }
        }


        .table-hover tbody tr:hover {
            background-color: #f1f7fb;
        }

        .progress-bar {
            transition: width 0.5s ease-in-out;
        }

        .scoreboard-btn {
            padding: 15px 30px;
            background: rgb(178, 255, 201);
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
            font-size: 18px;
        }

        .nav-pills .nav-link.active,
        .nav-pills .show>.nav-link {
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
            background: linear-gradient(180deg, #1e3c72, #2a5298);
        }

        .nav-pills.nav-item {
            border-bottom: 1px solid #829bc9;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-{{ $match_result != null ? '8' : '12' }}">
                    <div class="card mb-4" style="border: 1px solid #00b88a;">
                        <div class="card-body">
                            <div class="row g-4 align-items-center">
                                @if ($match->tournament && $match->tournament->logo)
                                    <div class="col-md-2 text-center">
                                        <div class="rounded border p-2 bg-light">
                                            <img src="{{ asset('storage/uploads/tournaments/' . $match->tournament->logo) }}"
                                                class="img-fluid rounded" style="max-height: 100px;"
                                                alt="{{ $match->tournament->name }}">
                                        </div>
                                    </div>
                                @endif

                                {{-- Match Info --}}
                                <div class="col-md-{{ $match->tournament && $match->tournament->logo ? '10' : '12' }}">
                                    <h4 class="fw-bolder mb-4 fs-28 text-center team-name">
                                        <span class="gradient-text">{{ $match->teamA->name ?? 'Team A' }}</span>
                                        <span class="team-versus">vs</span>
                                        <span class="gradient-text">{{ $match->teamB->name ?? 'Team B' }}</span>
                                    </h4>

                                    <div class="match-info-card shadow-sm rounded">
                                        <div class="info-row">
                                            <span class="info-label"><i class="ri-calendar-line me-2"></i>Match Date</span>
                                            <span
                                                class="info-value">{{ \Carbon\Carbon::parse($match->date)->format('d M, Y') ?? 'N/A' }}</span>
                                        </div>

                                        <div class="info-row">
                                            <span class="info-label"><i class="ri-trophy-line me-2"></i>Tournament</span>
                                            <span class="info-value">{{ $match->tournament->name ?? 'N/A' }}</span>
                                        </div>

                                        <div class="info-row">
                                            <span class="info-label"><i class="ri-map-pin-line me-2"></i>Venue</span>
                                            <span class="info-value">{{ $match->venue ?? 'No Venue available.' }}</span>
                                        </div>

                                        <div class="info-row">
                                            <span class="info-label"><i
                                                    class="ri-checkbox-circle-line me-2"></i>Status</span>
                                            <span class="info-value">
                                                <span
                                                    class="badge 
                                                        {{ $match->status == 'live' ? 'bg-danger' : ($match->status == 'completed' ? 'bg-success' : 'bg-secondary') }} px-3 py-2">
                                                    {{ ucfirst($match->status) }}
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                @if ($match_result != null)
                    <div class="col-4 match-show">
                        <div class="winner-wrap" id="winner-wrap">
                            <div class="border"></div>
                            <div class="medal-box"><i class="fas fa-medal"></i></div>
                            <h1 style="font-weight: bold;">{{ $match_result['winning_team'] }}</h1>
                            <h2>{{ $match_result['summary'] }}</h2>
                            <div class="winner-ribbon">WINNER</div>
                            <div class="right-ribbon"></div>
                            <div class="left-ribbon"></div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="card shadow-sm p-0">
                <div class="card-header bg-white p-0">
                    <ul class="nav nav-pills nav-justified" id="inningsTabs" role="tablist">
                        @foreach ($match->scoreboard as $index => $scoreboard)
                            <li class="nav-item" role="presentation">
                                <a href="#inning-{{ $index }}" data-bs-toggle="tab"
                                    class="nav-link @if ($index == 0) active @endif scoreboard-btn"
                                    id="inning-tab-{{ $index }}">
                                    {{ $scoreboard->team->name }} - Innings {{ $scoreboard->innings }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="card-body tab-content p-3">
                    @foreach ($inningsData as $i => $inning)
                        <div class="tab-pane fade @if ($i == 0) show active @endif"
                            id="inning-{{ $i }}">

                            <div class="score-summary rounded shadow-sm p-4 mb-4 position-relative"
                                style="
                                    border: 4px solid transparent;
                                    border-radius: 12px;
                                    background-image: 
                                        linear-gradient(0deg, #c3eeff, #bbd3ff), linear-gradient(135deg, #4a6cb3, #233a6b);
                                    background-origin: border-box;
                                    background-clip: padding-box, border-box;
                                    color: #002145;
                                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                                ">

                                {{-- Team Name --}}
                                <h4 class="mb-3 fw-bold text-uppercase text-center fs-28">
                                    {{ $inning['scoreboard']->team->name }}
                                </h4>

                                <div class="d-flex justify-content-around align-items-center text-center">
                                    <div class="score-box">
                                        <div class="score-value display-4 fw-bold">{{ $inning['scoreboard']->runs }}</div>
                                        <div class="score-label text-uppercase small">Runs</div>
                                    </div>
                                    <div class="score-box">
                                        <div class="score-value display-4 fw-bold">{{ $inning['scoreboard']->wickets }}
                                        </div>
                                        <div class="score-label text-uppercase small">Wickets</div>
                                    </div>
                                    <div class="score-box">
                                        <div class="score-value display-4 fw-bold">{{ $inning['scoreboard']->overs }}</div>
                                        <div class="score-label text-uppercase small">Overs</div>
                                    </div>
                                </div>

                                @php
                                    $overs = $inning['scoreboard']->overs;
                                    $runs = $inning['scoreboard']->runs;
                                    $currentRR = $overs > 0 ? round($runs / $overs, 2) : 0;
                                @endphp
                                <div class="text-center mt-3">
                                    <span class="badge bg-warning text-dark px-3 py-2 fs-6">
                                        Current Run Rate: {{ $currentRR }}
                                    </span>
                                </div>
                            </div>

                            {{-- Batting --}}
                            <div class="mb-5">
                                <h4 class="section-title">Batting</h4>
                                <table class="table table-hover table-bordered align-middle text-center">
                                    <thead class="table"
                                        style="background-color: #1e3c72!important;border-color: #354a72;">
                                        <tr>
                                            <th style="color: #fff!important;" class="text-start">Player</th>
                                            <th style="color: #fff!important;">Runs</th>
                                            <th style="color: #fff!important;">Balls</th>
                                            <th style="color: #fff!important;">Fours</th>
                                            <th style="color: #fff!important;">Sixes</th>
                                            <th style="color: #fff!important;">Strike Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($inning['batting'] as $bt)
                                            @if ($bt->balls_faced > 0)
                                                <tr>
                                                    <td class="text-start d-flex align-items-center">
                                                        <img src="{{ $bt->player->user->image ?? 'https://via.placeholder.com/40' }}"
                                                            class="rounded-circle me-2" width="40" height="40"
                                                            style="object-fit: cover;">
                                                        {{ $bt->player->user->full_name ?? 'Unknown' }}
                                                    </td>
                                                    <td>{{ $bt->runs_scored }}</td>
                                                    <td>{{ $bt->balls_faced }}</td>
                                                    <td>{{ $bt->fours }}</td>
                                                    <td>{{ $bt->sixes }}</td>
                                                    <td>{{ $bt->balls_faced ? round(($bt->runs_scored / $bt->balls_faced) * 100, 2) : 0 }}
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Bowling --}}
                            <div class="mb-5">
                                <h4 class="section-title">Bowling</h4>
                                <table class="table table-hover table-bordered align-middle text-center">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-start">Player</th>
                                            <th>Overs</th>
                                            <th>Maiden</th>
                                            <th>Runs</th>
                                            <th>Wickets</th>
                                            <th>Economy</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($inning['bowling'] as $b)
                                            @if ($b->overs_bowled > 0)
                                                <tr>
                                                    <td class="text-start d-flex align-items-center">
                                                        <img src="{{ $b->player->user->image ?? 'https://via.placeholder.com/40' }}"
                                                            class="rounded-circle me-2" width="40" height="40"
                                                            style="object-fit: cover;">
                                                        {{ $b->player->user->full_name ?? 'Unknown' }}
                                                    </td>
                                                    <td>{{ $b->overs_bowled }}</td>
                                                    <td>{{ $b->maidens ?? 0 }}</td>
                                                    <td>{{ $b->runs_conceded }}</td>
                                                    <td>{{ $b->wickets_taken }}</td>
                                                    <td>{{ $b->overs_bowled ? round($b->runs_conceded / $b->overs_bowled, 2) : 0 }}
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Partnerships --}}
                            <div class="mb-5">
                                <h4 class="section-title">Partnerships</h4>
                                <table class="table table-borderless w-100">
                                    @foreach ($inning['partnerships'] as $partnership)
                                        <tr>
                                            <td class="text-start">
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $partnership->batter1->user->image ?? 'https://via.placeholder.com/40' }}"
                                                        class="rounded-circle me-2" width="40" height="40"
                                                        style="object-fit: cover;">
                                                    <div>
                                                        <strong>{{ $partnership->batter1->user->full_name }}</strong>
                                                        <div class="text-muted">
                                                            {{ strtoupper(str_replace('-', ' ', $partnership->batter1->batting_style)) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center" style="min-width:200px;">
                                                @php
                                                    $totalRuns = max(
                                                        1,
                                                        ($partnership->player1_runs ?? 0) +
                                                            ($partnership->player2_runs ?? 0),
                                                    );
                                                    $batter1Percent = round(
                                                        (($partnership->player1_runs ?? 0) / $totalRuns) * 100,
                                                    );
                                                    $batter2Percent = 100 - $batter1Percent;
                                                @endphp
                                                <div class="mb-1"><small>{{ $partnership->runs }}
                                                        ({{ $partnership->balls }} balls)
                                                    </small></div>
                                                <div class="progress" style="height: 10px; border-radius:5px;">
                                                    <div class="progress-bar bg-warning"
                                                        style="width: {{ $batter1Percent ?? 50 }}%"></div>
                                                    <div class="progress-bar bg-success"
                                                        style="width: {{ $batter2Percent ?? 50 }}%"></div>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex align-items-center justify-content-end">
                                                    <div class="text-end me-2">
                                                        <strong>{{ $partnership->batter2->user->full_name }}</strong>
                                                        <div class="text-muted">
                                                            {{ strtoupper(str_replace('-', ' ', $partnership->batter2->batting_style)) }}
                                                        </div>
                                                    </div>
                                                    <img src="{{ $partnership->batter2->user->image ?? 'https://via.placeholder.com/40' }}"
                                                        class="rounded-circle" width="40" height="40"
                                                        style="object-fit: cover;">
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>

                            {{-- Fall of Wickets --}}
                            <div class="mb-5">
                                <h4 class="section-title">Fall of Wickets</h4>
                                <table class="table table-hover table-bordered text-center">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Batsman</th>
                                            <th>Score</th>
                                            <th>Overs</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($inning['fall_of_wickets'] as $fow)
                                            <tr>
                                                <td class="text-start">{{ $fow->batter->user->full_name ?? 'Unknown' }}
                                                </td>
                                                <td>{{ $fow->runs }}-{{ $fow->wicket_number }}</td>
                                                <td>{{ $fow->overs }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
@endpush
