@extends('admin.layouts.theme')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link href="{{ asset('storage/backend/css/scoreboard.css') }}" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e3c72;
            --primary-dark: #162b56;
            --primary-light: #2a5298;
            --secondary-color: #00b88a;
            --secondary-dark: #009670;
            --accent-color: #ffcc00;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
            --light-text: #f8f9fa;
            --border-color: #dee2e6;
        }

        .tab-pane {
            display: none !important;
        }

        .tab-pane.show.active {
            display: block !important;
        }

        .section-title {
            font-weight: 700;
            font-size: 1.25rem;
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: var(--light-text);
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-transform: uppercase;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            letter-spacing: 1px;
            border-left: 5px solid var(--accent-color);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(30, 60, 114, 0.05);
        }

        .progress-bar {
            transition: width 0.5s ease-in-out;
        }

        .scoreboard-btn {
            padding: 15px 30px;
            background: rgba(178, 255, 201, 0.9);
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
            font-size: 18px;
            color: var(--primary-dark);
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
        }

        .scoreboard-btn:hover {
            background: rgba(178, 255, 201, 1);
            color: var(--primary-color);
        }

        .nav-pills .nav-link.active,
        .nav-pills .show>.nav-link {
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
            background: linear-gradient(180deg, var(--primary-color), var(--primary-light));
            color: white !important;
            font-weight: 700;
            box-shadow: 0 4px 8px rgba(30, 60, 114, 0.3);
        }

        .nav-pills.nav-item {
            border-bottom: 2px solid var(--secondary-color);
        }

        /* Match info card */
        .match-info-card {
            background: linear-gradient(135deg, var(--light-bg) 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 12px;
            margin-top: 15px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(222, 226, 230, 0.5);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: var(--primary-color);
            display: flex;
            align-items: center;
        }

        .info-label i {
            color: var(--secondary-color);
            width: 20px;
        }

        .info-value {
            color: var(--dark-text);
            font-weight: 500;
        }

        /* Team names */
        .team-name {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .team-versus {
            margin: 0 15px;
            color: var(--primary-dark);
            font-weight: 700;
            font-size: 1.2em;
        }

        /* Score summary */
        .score-summary {
            padding: 25px 20px !important;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
            border: 3px solid var(--primary-color) !important;
            border-radius: 15px !important;
            box-shadow: 0 6px 15px rgba(30, 60, 114, 0.15) !important;
        }

        .score-box {
            padding: 15px;
            min-width: 100px;
            transition: transform 0.3s ease;
        }

        .score-box:hover {
            transform: translateY(-5px);
        }

        .score-value {
            font-size: 3rem !important;
            line-height: 1;
            color: var(--primary-color);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .score-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--primary-dark);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
        }

        /* Tables */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .table thead {
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
        }

        .table thead th {
            color: white !important;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            padding: 15px 12px !important;
        }

        .table tbody tr {
            border-bottom: 1px solid rgba(222, 226, 230, 0.5);
            transition: all 0.2s ease;
        }

        .table tbody tr:last-child {
            border-bottom: none;
        }

        .table tbody td {
            padding: 12px !important;
            vertical-align: middle;
            border: none;
        }

        .table tbody tr:nth-child(even) {
            background-color: rgba(248, 249, 250, 0.5);
        }

        .table tbody tr:hover {
            background-color: rgba(30, 60, 114, 0.08);
            transform: translateX(2px);
        }

        /* Progress bars for partnerships */
        .progress {
            height: 12px;
            border-radius: 6px;
            background-color: rgba(222, 226, 230, 0.3);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .progress-bar.bg-warning {
            background: linear-gradient(90deg, var(--warning-color), #ffdb58) !important;
        }

        .progress-bar.bg-success {
            background: linear-gradient(90deg, var(--secondary-color), #00d4a0) !important;
        }

        /* Badges */
        .badge {
            padding: 8px 15px;
            font-weight: 600;
            font-size: 0.9em;
            border-radius: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .badge.bg-success {
            background: linear-gradient(45deg, var(--secondary-color), var(--secondary-dark)) !important;
            border: none;
        }

        .badge.bg-warning {
            background: linear-gradient(45deg, var(--warning-color), #e0a800) !important;
            color: #212529;
            border: none;
        }

        /* Winner section */
        .winner-wrap {
            background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);
            border: 3px solid var(--accent-color);
            border-radius: 15px;
            padding: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(255, 204, 0, 0.2);
        }

        .winner-wrap h1 {
            color: var(--primary-color);
            font-weight: 800;
            margin-bottom: 10px;
        }

        .winner-wrap h2 {
            color: var(--secondary-dark);
            font-weight: 600;
        }

        .winner-ribbon {
            background: linear-gradient(45deg, var(--accent-color), #ffdb58);
            color: var(--primary-color);
        }

        .medal-box i {
            color: var(--accent-color);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Player images */
        .player-name img {
            border: 2px solid var(--primary-light);
            box-shadow: 0 2px 4px rgba(30, 60, 114, 0.2);
        }

        /* Partnership mobile */
        .partnership-row {
            background: linear-gradient(135deg, var(--light-bg) 0%, #e9ecef 100%);
            border-radius: 12px;
            margin-bottom: 15px;
            padding: 20px;
            border: 2px solid var(--border-color);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .partnership-row:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            border-color: var(--secondary-color);
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .col-8,
            .col-4 {
                width: 100% !important;
                max-width: 100% !important;
                flex: 0 0 100% !important;
            }

            .team-name {
                font-size: 1.3rem !important;
            }

            .score-value {
                font-size: 2.2rem !important;
            }

            .score-label {
                font-size: 0.8rem;
            }

            .section-title {
                font-size: 1.1rem;
                padding: 10px 15px;
            }

            .card-body {
                padding: 15px !important;
            }

            .table th,
            .table td {
                padding: 10px 8px !important;
                font-size: 0.85rem;
            }

            .scoreboard-btn {
                padding: 12px 15px;
                font-size: 14px;
                white-space: nowrap;
            }

            .nav-pills {
                flex-wrap: nowrap;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .nav-item {
                min-width: 200px;
                flex-shrink: 0;
            }

            .winner-wrap {
                transform: scale(0.95);
                transform-origin: top center;
                margin: 10px 0;
                padding: 20px;
            }

            .match-show {
                margin-top: 20px;
            }

            .score-summary {
                padding: 20px 15px !important;
            }
        }

        @media (max-width: 576px) {
            .section-title {
                font-size: 1rem;
                padding: 10px 12px;
            }

            .score-value {
                font-size: 1.8rem !important;
            }

            .team-name {
                font-size: 1.2rem !important;
            }

            .info-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .info-value {
                margin-top: 5px;
                font-size: 0.95rem;
            }

            .score-box {
                min-width: 80px;
                padding: 10px 5px;
            }

            .table th,
            .table td {
                padding: 8px 6px !important;
                font-size: 0.8rem;
            }

            .player-name img {
                width: 35px !important;
                height: 35px !important;
                margin-right: 10px !important;
            }

            .winner-wrap h1 {
                font-size: 1.5rem !important;
            }

            .winner-wrap h2 {
                font-size: 1.2rem !important;
            }
        }

        /* Hide some columns on mobile */
        @media (max-width: 768px) {
            .hide-on-mobile {
                display: none !important;
            }
        }

        /* Partnership mobile layout */
        @media (max-width: 768px) {
            .partnership-mobile {
                display: block !important;
            }

            .partnership-desktop {
                display: none !important;
            }
        }

        @media (min-width: 769px) {
            .partnership-mobile {
                display: none !important;
            }

            .partnership-desktop {
                display: table !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-12 col-lg-{{ $match_result != null ? '8' : '12' }}">
                    <div class="card mb-4" style="border: 2px solid var(--secondary-color);">
                        <div class="card-body">
                            <div class="row g-4 align-items-center">
                                @if ($match->tournament && $match->tournament->logo)
                                    <div class="col-12 col-md-2 text-center mb-3 mb-md-0">
                                        <div class="rounded border p-2" style="background: var(--light-bg); border-color: var(--secondary-color)!important;">
                                            <img src="{{ asset('storage/uploads/tournaments/' . $match->tournament->logo) }}"
                                                class="img-fluid rounded" style="max-height: 80px;"
                                                alt="{{ $match->tournament->name }}">
                                        </div>
                                    </div>
                                @endif

                                {{-- Match Info --}}
                                <div
                                    class="col-12 col-md-{{ $match->tournament && $match->tournament->logo ? '10' : '12' }}">
                                    <h4 class="fw-bolder mb-3 mb-md-4 fs-28 text-center team-name">
                                        <span class="gradient-text">{{ $match->teamA->name ?? 'Team A' }}</span>
                                        <span class="team-versus">vs</span>
                                        <span class="gradient-text">{{ $match->teamB->name ?? 'Team B' }}</span>
                                    </h4>

                                    <div class="match-info-card">
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
                                            <span class="info-label"><i class="ri-checkbox-circle-line me-2"></i>Status</span>
                                            <span class="info-value">
                                                <span class="badge 
                                                    {{ $match->status == 'live' ? 'bg-danger' : ($match->status == 'completed' ? 'bg-success' : 'bg-secondary') }} 
                                                    px-3 py-2">
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
                    <div class="col-12 col-lg-4 match-show">
                        <div class="winner-wrap" id="winner-wrap">
                            <div class="border"></div>
                            <div class="medal-box"><i class="fas fa-medal"></i></div>
                            <h1 style="font-weight: 800;">{{ $match_result['winning_team'] }}</h1>
                            <h2 style="font-weight: 600;">{{ $match_result['summary'] }}</h2>
                            <div class="winner-ribbon">WINNER</div>
                            <div class="right-ribbon"></div>
                            <div class="left-ribbon"></div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="card shadow-sm p-0 border-0">
                <div class="card-header bg-white p-0 border-bottom">
                    <ul class="nav nav-pills nav-justified" id="inningsTabs" role="tablist">
                        @foreach ($match->scoreboard as $index => $scoreboard)
                            <li class="nav-item" role="presentation">
                                <a href="#inning-{{ $index }}" data-bs-toggle="tab"
                                    class="nav-link @if ($index == 0) active @endif scoreboard-btn"
                                    id="inning-tab-{{ $index }}">
                                    <span class="d-none d-md-inline">{{ $scoreboard->team->name }} - </span>
                                    Innings {{ $scoreboard->innings }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="card-body tab-content p-2 p-md-3">
                    @foreach ($inningsData as $i => $inning)
                        <div class="tab-pane fade @if ($i == 0) show active @endif"
                            id="inning-{{ $i }}">

                            <div class="score-summary mb-4">
                                {{-- Team Name --}}
                                <h4 class="mb-3 fw-bold text-uppercase text-center fs-24 fs-md-28" style="color: var(--primary-color);">
                                    {{ $inning['scoreboard']->team->name }}
                                </h4>

                                <div class="d-flex justify-content-around align-items-center text-center flex-wrap">
                                    <div class="score-box mb-2 mb-md-0">
                                        <div class="score-value display-4 fw-bold">{{ $inning['scoreboard']->runs }}</div>
                                        <div class="score-label text-uppercase small">Runs</div>
                                    </div>
                                    <div class="score-box mb-2 mb-md-0">
                                        <div class="score-value display-4 fw-bold">{{ $inning['scoreboard']->wickets }}
                                        </div>
                                        <div class="score-label text-uppercase small">Wickets</div>
                                    </div>
                                    <div class="score-box mb-2 mb-md-0">
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
                                        Run Rate: {{ $currentRR }}
                                    </span>
                                </div>
                            </div>

                            {{-- Batting --}}
                            <div class="mb-4 mb-md-5">
                                <h4 class="section-title">Batting</h4>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle text-center">
                                        <thead>
                                            <tr>
                                                <th class="text-start">Player</th>
                                                <th>Runs</th>
                                                <th>Balls</th>
                                                <th class="hide-on-mobile">Fours</th>
                                                <th class="hide-on-mobile">Sixes</th>
                                                <th>SR</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($inning['batting'] as $bt)
                                                @if ($bt->balls_faced > 0)
                                                    <tr>
                                                        <td class="text-start d-flex align-items-center player-name">
                                                            <img src="{{ $bt->player->user->image ?? 'https://via.placeholder.com/40' }}"
                                                                class="rounded-circle me-2" width="40" height="40"
                                                                style="object-fit: cover;">
                                                            <div class="player-info">
                                                                <div class="fw-medium">
                                                                    {{ $bt->player->user->full_name ?? 'Unknown' }}</div>
                                                                <small class="text-muted d-block d-md-none">
                                                                    {{ $bt->fours }}×4, {{ $bt->sixes }}×6
                                                                </small>
                                                            </div>
                                                        </td>
                                                        <td class="fw-bold">{{ $bt->runs_scored }}</td>
                                                        <td>{{ $bt->balls_faced }}</td>
                                                        <td class="hide-on-mobile">{{ $bt->fours }}</td>
                                                        <td class="hide-on-mobile">{{ $bt->sixes }}</td>
                                                        <td>
                                                            <span class="badge bg-light text-dark">
                                                                {{ $bt->balls_faced ? round(($bt->runs_scored / $bt->balls_faced) * 100, 2) : 0 }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Bowling --}}
                            <div class="mb-4 mb-md-5">
                                <h4 class="section-title">Bowling</h4>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle text-center">
                                        <thead>
                                            <tr>
                                                <th class="text-start">Player</th>
                                                <th>Overs</th>
                                                <th class="hide-on-mobile">Maiden</th>
                                                <th>Runs</th>
                                                <th>Wkts</th>
                                                <th>Econ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($inning['bowling'] as $b)
                                                @if ($b->overs_bowled > 0)
                                                    <tr>
                                                        <td class="text-start d-flex align-items-center player-name">
                                                            <img src="{{ $b->player->user->image ?? 'https://via.placeholder.com/40' }}"
                                                                class="rounded-circle me-2" width="40" height="40"
                                                                style="object-fit: cover;">
                                                            <div>{{ $b->player->user->full_name ?? 'Unknown' }}</div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-light text-dark">
                                                                {{ $b->overs_bowled }}
                                                            </span>
                                                        </td>
                                                        <td class="hide-on-mobile">
                                                            <span class="badge bg-light text-dark">
                                                                {{ $b->maidens ?? 0 }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $b->runs_conceded }}</td>
                                                        <td>
                                                            <span class="badge bg-light text-dark">
                                                                {{ $b->wickets_taken }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge 
                                                                @if($b->overs_bowled > 0 && ($b->runs_conceded / $b->overs_bowled) < 6) 
                                                                    bg-success text-white
                                                                @elseif($b->overs_bowled > 0 && ($b->runs_conceded / $b->overs_bowled) < 8)
                                                                    bg-warning text-dark
                                                                @else
                                                                    bg-danger text-white
                                                                @endif">
                                                                {{ $b->overs_bowled ? round($b->runs_conceded / $b->overs_bowled, 2) : 0 }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Partnerships --}}
                            <div class="mb-4 mb-md-5">
                                <h4 class="section-title">Partnerships</h4>

                                <!-- Desktop View -->
                                <table class="table table-borderless w-100 partnership-desktop">
                                    @foreach ($inning['partnerships'] as $partnership)
                                        <tr class="partnership-row">
                                            <td class="text-start">
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $partnership->batter1->user->image ?? 'https://via.placeholder.com/40' }}"
                                                        class="rounded-circle me-2" width="40" height="40"
                                                        style="object-fit: cover;">
                                                    <div>
                                                        <strong style="color: var(--primary-color);">{{ $partnership->batter1->user->full_name }}</strong>
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
                                                <div class="mb-2">
                                                    <span class="badge bg-primary text-white">
                                                        {{ $partnership->runs }} runs ({{ $partnership->balls }} balls)
                                                    </span>
                                                </div>
                                                <div class="progress" style="height: 12px; border-radius:6px;">
                                                    <div class="progress-bar bg-warning"
                                                        style="width: {{ $batter1Percent ?? 50 }}%"
                                                        title="Batter 1: {{ $batter1Percent }}%">
                                                    </div>
                                                    <div class="progress-bar bg-success"
                                                        style="width: {{ $batter2Percent ?? 50 }}%"
                                                        title="Batter 2: {{ $batter2Percent }}%">
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex align-items-center justify-content-end">
                                                    <div class="text-end me-2">
                                                        <strong style="color: var(--primary-color);">{{ $partnership->batter2->user->full_name }}</strong>
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

                                <!-- Mobile View -->
                                <div class="partnership-mobile">
                                    @foreach ($inning['partnerships'] as $partnership)
                                        <div class="partnership-row">
                                            <div class="partnership-batters">
                                                <div class="partnership-batter">
                                                    <img src="{{ $partnership->batter1->user->image ?? 'https://via.placeholder.com/40' }}"
                                                        class="rounded-circle me-2" width="40" height="40"
                                                        style="object-fit: cover;">
                                                    <div>
                                                        <strong style="color: var(--primary-color);">{{ $partnership->batter1->user->full_name }}</strong>
                                                        <div class="text-muted small">
                                                            {{ strtoupper(str_replace('-', ' ', $partnership->batter1->batting_style)) }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="partnership-batter justify-content-end">
                                                    <div class="text-end me-2">
                                                        <strong style="color: var(--primary-color);">{{ $partnership->batter2->user->full_name }}</strong>
                                                        <div class="text-muted small">
                                                            {{ strtoupper(str_replace('-', ' ', $partnership->batter2->batting_style)) }}
                                                        </div>
                                                    </div>
                                                    <img src="{{ $partnership->batter2->user->image ?? 'https://via.placeholder.com/40' }}"
                                                        class="rounded-circle" width="40" height="40"
                                                        style="object-fit: cover;">
                                                </div>
                                            </div>

                                            <div class="partnership-info">
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
                                                <div class="mb-3">
                                                    <span class="badge bg-primary text-white p-2">
                                                        {{ $partnership->runs }} runs ({{ $partnership->balls }} balls)
                                                    </span>
                                                </div>
                                                <div class="progress" style="height: 10px; border-radius:5px;">
                                                    <div class="progress-bar bg-warning"
                                                        style="width: {{ $batter1Percent ?? 50 }}%">
                                                    </div>
                                                    <div class="progress-bar bg-success"
                                                        style="width: {{ $batter2Percent ?? 50 }}%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Fall of Wickets --}}
                            <div class="mb-4 mb-md-5">
                                <h4 class="section-title">Fall of Wickets</h4>
                                <div class="table-responsive">
                                    <table class="table table-hover text-center">
                                        <thead>
                                            <tr>
                                                <th>Batsman</th>
                                                <th>Score</th>
                                                <th>Overs</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($inning['fall_of_wickets'] as $fow)
                                                <tr>
                                                    <td class="text-start">
                                                        {{ $fow->batter->user->full_name ?? 'Unknown' }}
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">
                                                            {{ $fow->runs }}-{{ $fow->wicket_number }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $fow->overs }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            if ($(window).width() < 768) {
                $('#inningsTabs').addClass('flex-nowrap');
            }

            $(window).resize(function() {
                if ($(window).width() < 768) {
                    $('#inningsTabs').addClass('flex-nowrap');
                } else {
                    $('#inningsTabs').removeClass('flex-nowrap');
                }
            });
        });
    </script>
@endpush