@extends('admin.layouts.theme')

@section('content')
    @push('styles')
        <style>
            .player-wrapper {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
            }

            .input-card {
                position: relative;
                flex: 1 1 calc(50% - 15px);
                /* 2 cards per row with gap */
                background: #f9f9f9;
                border-radius: 12px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                transition: transform 0.3s, box-shadow 0.3s;
                cursor: pointer;
                min-height: 100px;
                padding: 15px;
            }

            .input-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
            }

            .input {
                position: absolute;
                opacity: 0;
                width: 100%;
                height: 100%;
                cursor: pointer;
                z-index: 2;
            }

            .check::before {
                content: '';
                position: absolute;
                top: 15px;
                right: 15px;
                width: 20px;
                height: 20px;
                border: 2px solid #ccc;
                border-radius: 50%;
                background: #fff;
                z-index: 1;
            }

            .input:checked+.check::after {
                content: '';
                position: absolute;
                top: 19px;
                right: 19px;
                width: 12px;
                height: 12px;
                background-color: #007bff;
                border-radius: 50%;
                z-index: 1;
            }

            .label {
                position: relative;
                z-index: 0;
                display: flex;
                flex-direction: column;
                justify-content: center;
                height: 100%;
            }

            .label .title {
                font-weight: 700;
                font-size: 16px;
                color: #333;
                margin-bottom: 5px;
            }

            .label .score {
                font-weight: 500;
                font-size: 14px;
                color: #555;
            }

            .over-display {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .ball {
                width: 35px;
                height: 35px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 16px;
                color: white;
                box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .ball:hover {
                transform: scale(1.15);
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            }

            .dot-ball {
                background-color: #b0b0b0;
            }

            .run-ball {
                background-color: #28a745;
            }

            .extra-ball {
                background-color: #F08B51;
            }

            .six-ball {
                background-color: #16C47F;
            }

            .four-ball {
                background-color: #1E93AB;
            }

            .wicket-ball {
                background-color: #dc3545;
            }

            .text-right {
                text-align: right !important;
            }

            .radio-inputs {
                display: flex;
                flex-direction: row;
                justify-content: center;
                align-items: center;
                max-width: 100%;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
            }

            .radio-inputs>* {
                margin: 10px 0;
                /* Add vertical spacing between options */
            }

            .radio-input:checked+.radio-tile {
                border-color: #328e6e;
                box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
                color: #328e6e;
            }

            .radio-input:checked+.radio-tile:before {
                transform: scale(1);
                opacity: 1;
                background-color: #328e6e;
                border-color: #328e6e;
            }

            .radio-input:checked+.radio-tile .radio-icon svg {
                fill: #328e6e;
            }

            .radio-input:checked+.radio-tile .radio-label {
                color: #328e6e;
            }

            .radio-input:focus+.radio-tile {
                border-color: #328e6e;
                box-shadow:
                    0 5px 10px rgba(0, 0, 0, 0.1),
                    0 0 0 4px #b5c9fc;
            }

            .radio-input:focus+.radio-tile:before {
                transform: scale(1);
                opacity: 1;
            }

            .radio-tile {
                display: flex;
                flex-direction: row;
                align-items: center;
                justify-content: flex-start;
                width: 180px;
                /* Width of the pill */
                min-height: 50px;
                /* Height of the pill */
                border-radius: 25px;
                /* Full pill shape */
                border: 2px solid #b5bfd9;
                background-color: #fff;
                box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
                transition: 0.15s ease;
                cursor: pointer;
                position: relative;
                padding-left: 50px;
                /* Space for the indicator */
            }

            .radio-tile:before {
                content: "";
                position: absolute;
                display: block;
                width: 20px;
                /* Size of the indicator */
                height: 20px;
                /* Size of the indicator */
                border: 2px solid #b5bfd9;
                background-color: #fff;
                border-radius: 50%;
                /* Circle shape for the indicator */
                left: 15px;
                /* Position of the indicator */
                transition: 0.25s ease;
            }

            .radio-tile:hover {
                border-color: #328e6e;
            }

            .radio-tile:hover:before {
                transform: scale(1);
                opacity: 1;
            }

            .radio-icon {
                margin-left: 10px;
                /* Space between the indicator and icon */
            }

            .radio-icon svg {
                width: 1.5rem;
                height: 1.5rem;
                fill: #494949;
            }

            .radio-label {
                color: #707070;
                transition: 0.375s ease;
                text-align: left;
                /* Align text to the left */
                font-size: 13px;
                margin-left: 10px;
                /* Space between the icon and label */
            }

            .radio-input {
                clip: rect(0 0 0 0);
                -webkit-clip-path: inset(100%);
                clip-path: inset(100%);
                height: 1px;
                overflow: hidden;
                position: absolute;
                white-space: nowrap;
                width: 1px;
            }

            .score-badge {
                min-width: 120px;
                text-align: center;
                font-weight: 600;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
                transition: transform 0.2s;
            }

            .score-badge:hover {
                transform: scale(1.05);
            }
        </style>
    @endpush

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="row">
        <div class="col-sm-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
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
                                    <h4 class="fw-bold mb-2">
                                        {{ $match->teamA->name ?? 'Team A' }}
                                        <span class="text-muted">vs</span>
                                        {{ $match->teamB->name ?? 'Team B' }}
                                    </h4>

                                    <table class="table">
                                        <tr>
                                            <td width="8%" class="p-2 fs-16">Match Date</td>
                                            <td width="2%" class="p-2 fs-16">:</td>
                                            <td width="90%" class="p-2 fs-16">
                                                {{ \Carbon\Carbon::parse($match->date)->format('d M, Y') ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td width="8%" class="p-2 fs-16">Tournament</td>
                                            <td width="2%" class="p-2 fs-16">:</td>
                                            <td width="90%" class="p-2 fs-16">{{ $match->tournament->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td width="8%" class="p-2 fs-16">Venue</td>
                                            <td width="2%" class="p-2 fs-16">:</td>
                                            <td width="90%" class="p-2 fs-16">{{ $match->venue ?? 'No Venue available.' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="8%" class="p-2 align-middle fs-16">Status</td>
                                            <td width="2%" class="p-2 align-middle fs-16">:</td>
                                            <td width="90%" class="p-2 fs-16">
                                                <select name="start-match" id="start-match" class="form-control select2 w-25 border-cyan">
                                                    <option value="live">Live</option>
                                                    <option value="completed">Completed</option>
                                                    <option value="upcoming">Upcoming</option>
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <h4 class="text-center"
                                style="font-weight: bold; border-bottom: 2px solid #3f3f3f;border-top: 2px solid #3f3f3f; padding: 6px 20px;border-radius: 5px;margin-bottom: 15px;">
                                Match Toss</h4>

                            <table>
                                <tr>
                                    <td>
                                        <h4 class="text-left" style=" margin: 0;margin-right: 10px;">Team</h4>
                                    </td>
                                    <td>
                                        <div class="radio-inputs d-flex">
                                            <input type="hidden" name="toss_match_id" value="{{ $match->id }}">
                                            <input type="hidden" name="innings" value="">
                                            <input type="hidden" name="max_overs" value="{{ $match->max_overs }}">
                                            <input type="hidden" id="bowling_team_id">
                                            <input type="hidden" name="battingTeamId">

                                            <label style="margin-right: 15px;">
                                                <input name="toss-team" type="radio" class="radio-input"
                                                    value="{{ $match->teamA->id }}"
                                                    @if ($match->toss && $match->toss->toss_winner_team_id == $match->teamA->id) checked @endif />
                                                <span class="radio-tile">
                                                    <span class="radio-label">{{ $match->teamA->name }}</span>
                                                </span>
                                            </label>
                                            <label style="margin-left: 15px;">
                                                <input name="toss-team" type="radio" class="radio-input"
                                                    value="{{ $match->teamB->id }}"
                                                    @if ($match->toss && $match->toss->toss_winner_team_id == $match->teamB->id) checked @endif />
                                                <span class="radio-tile">
                                                    <span class="radio-label">{{ $match->teamB->name }}</span>
                                                </span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h4 class="text-left" style=" margin: 0;margin-right: 10px;">Decision</h4>
                                    </td>
                                    <td>
                                        <div class="radio-inputs d-flex">
                                            <label style="margin-right: 15px;">
                                                <input name="toss-decision" type="radio" class="radio-input"
                                                    value="BAT" @if ($match->toss && $match->toss->decision == 'bat') checked @endif />
                                                <span class="radio-tile">
                                                    <span class="radio-label">BAT</span>
                                                </span>
                                            </label>
                                            <label style="margin-left: 15px;">
                                                <input name="toss-decision" type="radio" class="radio-input"
                                                    value="BOWL" @if ($match->toss && $match->toss->decision == 'bowl') checked @endif />
                                                <span class="radio-tile">
                                                    <span class="radio-label">BOWL</span>
                                                </span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" id="match-scoreboard" class="d-block">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">

                        <!-- Left: Team & Score Info -->
                        <div class="me-3 flex-grow-1">
                            <h4 class="mb-3 fw-bold text-primary d-flex align-items-center">
                                <i class="ri-trophy-line me-3 fs-28"></i>
                                <span id="battingTeamName">Team A</span>
                            </h4>

                            <div class="d-flex flex-wrap gap-3">
                                <div class="score-badge bg-success text-white px-4 py-2 rounded">
                                    <strong>Score</strong><br>
                                    <span id="currentScore" class="fs-18">0 / 0</span>
                                </div>

                                <div class="score-badge bg-info text-white px-4 py-2 rounded">
                                    <strong>Overs</strong><br>
                                    <span id="currentOvers" class="fs-18">0.0</span>
                                </div>

                                <div class="score-badge bg-warning text-dark px-4 py-2 rounded">
                                    <strong>CRR</strong><br>
                                    <span id="currentCRR" class="fs-18">0.0</span>
                                </div>

                                <div class="score-badge bg-secondary text-white px-4 py-2 rounded">
                                    <strong>Projected</strong><br>
                                    <span id="projectedScore" class="fs-18">00</span>
                                </div>

                                <div
                                    class="score-badge bg-primary text-white px-4 py-2 rounded tagetscore-container d-none">
                                    <strong>Target</strong><br>
                                    <span id="targetScore" class="fs-18">00</span>
                                </div>

                                <div
                                    class="score-badge bg-primary text-white px-4 py-2 rounded requiredRunRate-container d-none">
                                    <strong>RR</strong><br>
                                    <span id="requiredRunRate" class="fs-18">00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Action Button -->
                        <div class="ms-auto mt-2 mt-md-0">
                            <button class="btn btn-outline-danger btn-complete-innings btn-lg shadow-sm">
                                Complete Innings
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body row">
                    <div class="col-md-8">
                        <div class="row">
                            {{-- Player 1 --}}
                            <div class="col-md-6">
                                <div class="card mb-3 border-info border-1">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h5 id="strikerName">Choose Player</h5>
                                                <p class="m-0">Runs: <span id="strikerRuns">00</span> (<span
                                                        id="strikerBallsFaced">0</span> balls)</span></p>
                                            </div>
                                        </div>
                                        <div id="strikerActions">
                                            {{-- <button class="btn btn-danger btn-sm" onclick="selectBatsman()">On Strike</button> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Player 2 --}}
                            <div class="col-md-6">
                                <div class="card mb-3 border-info border-1">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 id="nonStrikerName">Choose Player</h5>
                                            <p class="m-0">Runs: <span id="nonStrikerRuns">00</span> (<span
                                                    id="nonStrikerBallsFaced">0</span> balls)</span></p>
                                        </div>
                                        <div class="d-flex flex-column" id="nonStrikerActions">
                                            {{-- button will be injected here dynamically --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Scoring buttons --}}
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex flex-wrap gap-3 mt-3">
                                    {{-- Runs --}}
                                    <div>
                                        <h5>Runs</h5>
                                        @foreach (['0', '1', '2', '3', '4', '6'] as $run)
                                            <button class="btn btn-outline-primary btn-run"
                                                data-run="{{ $run }}">{{ $run }}</button>
                                        @endforeach
                                    </div>

                                    {{-- Extras --}}
                                    <div>
                                        <h5>Extras</h5>
                                        @foreach (['NB', 'WD', 'LB', 'BY'] as $extra)
                                            <button type="button" class="btn btn-outline-warning btn-extra"
                                                data-bs-toggle="modal" data-bs-target="#extraModal"
                                                data-extra="{{ $extra }}">
                                                {{ $extra }}
                                            </button>
                                        @endforeach
                                    </div>

                                    {{-- Wickets --}}
                                    <div>
                                        <h5>Wickets</h5>
                                        <button class="btn btn-outline-danger btn-wicket" data-bs-toggle="modal"
                                            data-bs-target="#wicketModal" data-type="W">W</button>
                                    </div>

                                    <!-- Modal -->
                                    <div class="modal fade" id="extraModal" tabindex="-1"
                                        aria-labelledby="extraModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form id="extraForm">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="extraModalLabel">Extra</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <div class="modal-body">

                                                        {{-- NB Section --}}
                                                        <div id="nbSection" class="d-none">
                                                            <p><strong>No Ball:</strong> Select runs + run out option</p>
                                                            <div>
                                                                <label>Runs:</label>
                                                                <div class="btn-group" role="group"
                                                                    id="extraRunButtons">
                                                                    @foreach ([0, 1, 2, 3, 4, 6] as $r)
                                                                        <input type="radio" class="btn-check"
                                                                            name="nbRuns" id="nbRun{{ $r }}"
                                                                            value="{{ $r }}">
                                                                        <label class="btn btn-outline-primary"
                                                                            for="nbRun{{ $r }}">{{ $r }}</label>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                            <div class="mt-3">
                                                                <input type="checkbox" id="nbRunOut"
                                                                    class="form-check-input">
                                                                <label for="nbRunOut" class="form-check-label">Run
                                                                    Out?</label>
                                                            </div>
                                                            <div id="nbBatsmanOut" class="mt-2 d-none">
                                                                <label class="fs-14" style="font-weight: bold;">Batsman
                                                                    Out:</label>
                                                                <div class="player-wrapper">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- WD Section --}}
                                                        <div id="wdSection" class="d-none">
                                                            <p><strong>Wide Ball:</strong> Default 1 run counted. Add extras
                                                                if needed.</p>
                                                            <div>
                                                                <label>Extra Runs:</label>
                                                                <div class="btn-group" role="group">
                                                                    @foreach ([0, 1, 2, 3, 4, 5, 6] as $r)
                                                                        <input type="radio" class="btn-check"
                                                                            name="wdExtraRuns"
                                                                            id="wdRun{{ $r }}"
                                                                            value="{{ $r }}">
                                                                        <label class="btn btn-outline-primary"
                                                                            for="wdRun{{ $r }}">{{ $r }}</label>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- LB Section --}}
                                                        <div id="lbSection" class="d-none">
                                                            <p><strong>Leg Bye:</strong> Select runs</p>
                                                            <div>
                                                                <label>Runs:</label>
                                                                <div class="btn-group" role="group">
                                                                    @foreach ([0, 1, 2, 3, 4] as $r)
                                                                        <input type="radio" class="btn-check"
                                                                            name="lbRuns" id="lbRun{{ $r }}"
                                                                            value="{{ $r }}">
                                                                        <label class="btn btn-outline-primary"
                                                                            for="lbRun{{ $r }}">{{ $r }}</label>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Submit</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Over display --}}
                                <div class="mt-3 fs-16 d-flex align-items-center">
                                    <p class="m-0"><strong>Current Over:</strong></p>
                                    <div id="currentOverDetails" class="over-display" style="margin-left: 15px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <h4 style="font-weight: bold; border-radius: 5px;" class="bg-soft-info py-2 text-uppercase">
                                Batting
                            </h4>

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th width="50%" class="text-left font-weight-bold">Player Name</th>
                                        <th width="10%" class="text-center">R</th>
                                        <th width="10%" class="text-center">B</th>
                                        <th width="10%" class="text-center">4s</th>
                                        <th width="10%" class="text-center">6s</th>
                                        <th width="10%" class="text-center">SR</th>
                                    </tr>
                                </thead>
                                <tbody id="batting-stats">
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-4">
                            <div class="d-flex justify-content-between align-items-center bg-soft-info py-2"
                                style="border-radius: 5px;">
                                <h4 style="font-weight: bold; border-radius: 5px;" class="text-uppercase m-0">Bowling</h4>
                                <div id="add-bowler-row" class="w-25">
                                    <select id="bowler-select" style="width: 300px;">
                                        <option value="">Select Bowler</option>
                                    </select>
                                </div>
                            </div>

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th width="50%" class="text-left font-weight-bold">Player Name</th>
                                        <th width="10%" class="text-center">O</th>
                                        <th width="10%" class="text-center">M</th>
                                        <th width="10%" class="text-center">R</th>
                                        <th width="10%" class="text-center">W</th>
                                        <th width="10%" class="text-center">ER</th>
                                    </tr>
                                </thead>
                                <tbody id="bowling-stats">
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-4">
                            <h4 style="font-weight: bold; border-radius: 5px;" class="bg-soft-info py-2">PARTNERSHIP</h4>

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th width="35%" class="text-left">Batsman 1</th>
                                        <th width="30%" class="text-center"></th>
                                        <th width="35%" class="text-right" style="text-align: right">Batsman 2</th>
                                    </tr>
                                </thead>
                                <tbody id="partnership-stats">
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-4">
                            <h4 style="font-weight: bold; border-radius: 5px;" class="bg-soft-info py-2">FALL OF WICKETS
                            </h4>

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th width="50%" class="text-left">Batsman</th>
                                        <th width="10%" class="text-center">Score</th>
                                        <th width="10%" class="text-center">Overs</th>
                                    </tr>
                                </thead>
                                <tbody id="fallofwickets-stats">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <h4 style="font-weight: bold;">Yet to Bat</h4>
                        <div class="form-group mb-3">
                            <input type="text" name="filter-player" id="flt-player"
                                placeholder="Search by player name" class="form-control">
                        </div>
                        <div id="yetToBatList" style="display: flex; flex-direction: column;">
                            <p class="list-group-item alert alert-info">Loading...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" id="match-result" class="d-none">

            </div>
        </div>
    </div>

    {{-- Wicket Modal --}}
    <div class="modal fade" id="wicketModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Wicket Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <p class="fs-16 font-weight-bold m-0">Select Wicket Type:</p>
                        <button type="reset" class="btn btn-sm btn-warning" id="btn-reset-wicket-form">Reset</button>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <button class="btn btn-outline-danger btn-wicket-type" data-wicket="Bowled">Bowled</button>
                        <button class="btn btn-outline-danger btn-wicket-type" data-wicket="Caught">Catch</button>
                        <button class="btn btn-outline-danger btn-wicket-type" data-wicket="Run Out">Run Out</button>
                        <button class="btn btn-outline-danger btn-wicket-type" data-wicket="Stumped">Stumped</button>
                        <button class="btn btn-outline-danger btn-wicket-type"
                            data-wicket="Hit-Wicket">Hit-Wicket</button>
                        <button class="btn btn-outline-danger btn-wicket-type"
                            data-wicket="Retired-Hurt">Retired-Hurt</button>
                        <button class="btn btn-outline-danger btn-wicket-type" data-wicket="LBW">LBW</button>
                    </div>

                    <!-- Run Out Options -->
                    <div id="runOutOptions" class="mt-3 d-none wicket-extra">
                        <p>Select Batsman Out:</p>
                        <div class="d-flex flex-column gap-2">
                            <button class="btn btn-outline-secondary btn-batsman-out striker-btn"
                                data-batsman="striker">Striker</button>
                            <button class="btn btn-outline-secondary btn-batsman-out nonstriker-btn"
                                data-batsman="Non-Striker">Non-Striker</button>
                        </div>
                    </div>

                    <!-- Caught Options -->
                    <div id="caughtOptions" class="mt-3 d-none wicket-extra">
                        <p>Select Fielder (Who took the catch):</p>
                        <select class="form-select" id="caughtBySelect">
                            <!-- Dynamically load bowling team players -->
                        </select>
                    </div>

                    <!-- Stumped Options -->
                    <div id="stumpedOptions" class="mt-3 d-none wicket-extra">
                        <p>Select Keeper (Who stumped):</p>
                        <select class="form-select" id="stumpedBySelect">
                            <!-- Dynamically load bowling team players -->
                        </select>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let selectedTeam = null;
            let selectedDecision = null;
            let matchId = $('input[name="toss_match_id"]').val();
            let bowlingTeamPlayers = [];
            let matchState = {
                striker: null,
                nonStriker: null,
                battingTeamId: null
            };

            let strikerId = null;
            let nonStrikerId = null;
            const stateKey = "match_state_" + matchId;

            $("#start-match").select2({
                placeholder: "Select Status",
                width: "25%",
                minimumResultsForSearch: -1
            });

            $('#start-match').on('change', function() {
                var selectedStatus = $(this).val();
                console.log("Selected status:", selectedStatus);

                $.ajax({
                    url: '/admin/cricket-matches/start/'+matchId,
                    method: 'GET',
                    data: {
                        status: selectedStatus,
                    },
                    success: function(response) {
                        if(response.success){
                            Swal.fire({
                                icon: 'success',
                                title: 'Status Updated!',
                                text: `Match status has been changed to "${selectedStatus}".`,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(err) {
                        Swal.fire('Error', 'Failed to update match status.', 'error');
                    }
                });
            });


            // ------------------------
            // 🔹 Extra Runs
            // ------------------------
            document.querySelectorAll('.btn-extra').forEach(btn => {
                btn.addEventListener('click', function() {
                    let extra = this.dataset.extra;
                    document.querySelector('#extraModal .modal-title').innerText = "Selected: " +
                        extra;
                });
            });

            // ------------------------
            // 🔹 Strike Switch
            // -----------------------
            window.switchStrike = function() {
                fetch("{{ route('admin.cricket-matches.scoreboard.switch-strike') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            match_id: matchId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) throw new Error(data.message || 'Failed to switch strike');

                        // Update local state
                        matchState.striker = {
                            id: data.data.striker.id,
                            name: data.data.striker.name,
                            runs: data.data.striker.runs ?? 0,
                            balls: data.data.striker.balls ?? 0
                        };
                        matchState.nonStriker = {
                            id: data.data.nonStriker.id,
                            name: data.data.nonStriker.name,
                            runs: data.data.nonStriker.runs ?? 0,
                            balls: data.data.nonStriker.balls ?? 0
                        };

                        // Update UI
                        updatePlayerCard(matchState.striker, 'striker');
                        updatePlayerCard(matchState.nonStriker, 'nonStriker');

                        saveMatchState();
                    })
                    .catch(err => console.error("Error switching strike:", err));
            }

            function updateStrikeButtons() {
                document.querySelectorAll('.on-strike-btn').forEach(btn => btn.remove());

                const btn = document.createElement('button');
                btn.classList.add('btn', 'btn-info', 'btn-sm', 'on-strike-btn');
                btn.innerText = 'On Strike';
                btn.onclick = () => switchStrike();

                const nonStrikerContainer = document.getElementById('nonStrikerActions');
                if (nonStrikerContainer) nonStrikerContainer.appendChild(btn);
            }

            // Helper to update player UI
            function updatePlayerCard(player, type) {
                const cardId = type === 'striker' ? 'strikerName' : 'nonStrikerName';
                const runsId = type === 'striker' ? 'strikerRuns' : 'nonStrikerRuns';
                const ballsId = type === 'striker' ? 'strikerBallsFaced' : 'nonStrikerBallsFaced';
                const actionsId = type === 'striker' ? 'strikerActions' : 'nonStrikerActions';

                if (player) {
                    document.getElementById(cardId).innerText = player.name;
                    document.getElementById(runsId).innerText = player.runs ?? 0;
                    document.getElementById(ballsId).innerText = player.balls ?? 0;

                    if (type === 'nonStriker') {
                        document.getElementById(actionsId).innerHTML = `
                            <button class="btn btn-danger btn-sm" onclick="switchStrike()">On Strike</button>
                        `;
                    } else {
                        document.getElementById(actionsId).innerHTML = ''; // remove button for striker
                    }
                } else {
                    document.getElementById(cardId).innerText = 'Choose Player';
                    document.getElementById(runsId).innerText = '00';
                    document.getElementById(ballsId).innerText = '0';
                    document.getElementById(actionsId).innerHTML = '';
                }
            }

            // ------------------------
            // 🔹 Save match state to localStorage
            // ------------------------
            function saveMatchState() {
                localStorage.removeItem("match_state_" + matchId);
                const state = {
                    striker: strikerId ? {
                        id: strikerId,
                        name: document.getElementById("strikerName")?.innerText,
                        runs: document.getElementById("strikerRuns")?.innerText ?? "0",
                        balls: document.getElementById("strikerBallsFaced")?.innerText ?? "0",
                    } : null,

                    nonStriker: nonStrikerId ? {
                        id: nonStrikerId,
                        name: document.getElementById("nonStrikerName")?.innerText,
                        runs: document.getElementById("nonStrikerRuns")?.innerText ?? "0",
                        balls: document.getElementById("nonStrikerBallsFaced")?.innerText ?? "0",
                    } : null,

                    team: {
                        name: document.getElementById("battingTeamName")?.innerText,
                        score: document.getElementById("currentScore")?.innerText,
                        overs: document.getElementById("currentOvers")?.innerText,
                        crr: document.getElementById("currentCRR")?.innerText,
                        projected: document.getElementById("projectedScore")?.innerText
                    },

                    currentBowler: parseInt($('input[name="current-bowler"]:checked').attr('data-playerid')) ||
                        null,
                };

                localStorage.setItem("match_state_" + matchId, JSON.stringify(state));
            }

            // ------------------------
            // 🔹 Load saved state from localStorage
            // ------------------------
            function loadMatchState() {
                fetch("{{ route('admin.cricket-matches.scoreboard.match-info', ['match_id' => '']) }}" +
                        matchId, {
                            method: "GET",
                        })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            localStorage.setItem("match_state_" + matchId, JSON.stringify(data.match_state));
                            populateMatchState(data.match_state);
                        } else {
                            console.error("Failed to load match state:", data.message);
                        }
                    })
                    .catch(err => console.error("Error fetching match state:", err));
            }

            function populateMatchState(state) {
                // Striker
                if (state.striker) {
                    strikerId = state.striker.id;
                    $('.btn-batsman-out.striker-btn').attr('data-batsman', strikerId);
                    document.getElementById("strikerName").innerText = state.striker.name;
                    document.getElementById("strikerRuns").innerText = state.striker.runs;
                    document.getElementById("strikerBallsFaced").innerText = state.striker.balls;
                }

                // Non-striker
                if (state.nonStriker) {
                    nonStrikerId = state.nonStriker.id;
                    $('.btn-batsman-out.nonstriker-btn').attr('data-batsman', nonStrikerId);
                    document.getElementById("nonStrikerName").innerText = state.nonStriker.name;
                    document.getElementById("nonStrikerRuns").innerText = state.nonStriker.runs;
                    document.getElementById("nonStrikerBallsFaced").innerText = state.nonStriker.balls;

                    document.getElementById("nonStrikerActions").innerHTML = `
                        <button class="btn btn-danger btn-sm" onclick="switchStrike()">On Strike</button>
                    `;
                } else {
                    document.getElementById("nonStrikerActions").innerHTML = '';
                }

                // Team info
                if (state.team) {
                    document.getElementById("battingTeamName").innerText = state.team.name;
                    document.getElementById("currentScore").innerText = state.team.score + " / " + (state.team
                        .wickets || 0);
                    document.getElementById("currentOvers").innerText = state.team.overs;
                    document.getElementById("currentCRR").innerText = state.team.crr;
                    document.getElementById("projectedScore").innerText = state.team.projected;
                }

                // Current bowler
                if (state.currentBowler) {
                    const bowlerId = parseInt(state.currentBowler);
                    $('input[name="current-bowler"]').each(function() {
                        const pid = parseInt($(this).attr('data-playerid'));
                        $(this).prop('checked', pid === bowlerId);
                    });
                }
            }

            function loadCurrentPlayersToModal() {
                const state = JSON.parse(localStorage.getItem("match_state_" + matchId) || "{}");

                const players = [];
                if (state.striker) players.push(state.striker);
                if (state.nonStriker) players.push(state.nonStriker);

                const container = $('#nbBatsmanOut .player-wrapper');
                container.empty(); // Clear old entries

                players.forEach(player => {
                    const playerCard = `
                        <div class="input-card">
                            <input class="input player-id" type="radio" name="player_id" value="${player.id}">
                            <span class="check"></span>
                            <label class="label">
                                <div class="title">${player.name}</div>
                                <div class="score">Score: ${player.score}</div>
                            </label>
                        </div>`;
                    container.append(playerCard);
                });

                // Show the modal section if hidden
                container.removeClass('d-none');
            }

            // ------------------------
            // 🔹 Toss Selection
            // ------------------------
            $('input[name="toss-team"]').on('change', function() {
                selectedTeam = $(this).val();
                submitTossIfReady();
            });

            $('input[name="toss-decision"]').on('change', function() {
                selectedDecision = $(this).val();
                submitTossIfReady();
            });

            function submitTossIfReady() {
                if (selectedTeam && selectedDecision) {
                    $.ajax({
                        url: "{{ route('admin.cricket-matches.toss.store') }}",
                        method: "POST",
                        data: {
                            _token: '{{ csrf_token() }}',
                            match_id: matchId,
                            toss_winner_team_id: selectedTeam,
                            toss_decision: selectedDecision
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: response.message,
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true,
                                });

                                // ✅ Use batting_team_name instead of toss_winner_team_name
                                $('#battingTeamName').text(response.batting_team_name);

                                saveMatchState();
                            } else {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'error',
                                    title: response.message || 'Something went wrong',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true,
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: xhr.responseJSON.message || 'Something went wrong',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                            });
                        }
                    });
                }
            }

            // ------------------------
            // 🔹 Select Batsman
            // ------------------------
            window.selectBatsman = function(playerId) {
                const matchState = JSON.parse(localStorage.getItem("match_state_" + matchId) || "{}");
                const battingTeamId = matchState.battingTeamId;

                let role = null;

                // Determine role based on which player slot is empty
                if (!matchState.striker) {
                    role = 'on-strike';
                } else if (!matchState.nonStriker) {
                    role = 'batting';
                } else {
                    Swal.fire('Both striker and non-striker are already selected.');
                    return;
                }

                fetch("{{ route('admin.cricket-matches.scoreboard.select-batsman') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            match_id: matchId,
                            team_id: battingTeamId,
                            player_id: playerId,
                            role: role
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) throw new Error(data.message);

                        const player = data.data.match_player;

                        if (role === 'on-strike') {
                            matchState.striker = {
                                id: player.player_id,
                                name: player.player?.user?.full_name || player.full_name,
                                runs: 0,
                                balls: 0
                            };
                        } else if (role === 'batting') {
                            matchState.nonStriker = {
                                id: player.player_id,
                                name: player.player?.user?.full_name || player.full_name,
                                runs: 0,
                                balls: 0
                            };
                        }

                        localStorage.setItem("match_state_" + matchId, JSON.stringify(matchState));

                        // Update UI
                        if (role === 'on-strike') {
                            document.getElementById("strikerName").innerText = matchState.striker.name;
                            document.getElementById("strikerRuns").innerText = "00";
                            document.getElementById("strikerBallsFaced").innerText = "0";
                        } else {
                            document.getElementById("nonStrikerName").innerText = matchState.nonStriker
                                .name;
                            document.getElementById("nonStrikerRuns").innerText = "00";
                            document.getElementById("nonStrikerBallsFaced").innerText = "0";
                        }

                        // Remove player from Yet-To-Bat list
                        const card = document.querySelector(`[data-player-id="${playerId}"]`);
                        if (card) card.remove();
                        window.location.reload();
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Error', err.message, 'error');
                    });
            }

            function saveBatsman(matchId, teamId, playerId, status, callback) {
                fetch("{{ route('admin.cricket-matches.scoreboard.select-batsman') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            match_id: matchId,
                            team_id: teamId,
                            player_id: playerId,
                            role: status
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: data.message || 'Player saved successfully',
                                showConfirmButton: false,
                                timer: 2500,
                                timerProgressBar: true
                            });

                            if (callback) callback();
                        } else {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: data.message || 'Failed to save player',
                                showConfirmButton: false,
                                timer: 2500,
                                timerProgressBar: true
                            });
                            console.error("Failed to save player:", data);
                        }
                    })
                    .catch(err => {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'Error saving player',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true
                        });
                        console.error("Error saving player:", err);
                    });
            }

            // ------------------------
            // 🔹 Select Bowler
            // ------------------------
            window.selectBowler = function(playerId) {
                const state = JSON.parse(localStorage.getItem(stateKey) || "{}");
                state.currentBowler = playerId;
                localStorage.setItem(stateKey, JSON.stringify(state));
                fetch("{{ route('admin.cricket-matches.scoreboard.select-bowler') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            match_id: matchId,
                            team_id: state.battingTeamId,
                            player_id: playerId,
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) throw new Error(data.message);
                        window.location.reload();
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Error', err.message, 'error');
                    });
            }

            // Use delegation from the table body (or any static parent)
            $('#bowling-stats').on('change', 'input[name="current-bowler"]', function() {
                const playerId = $(this).data('playerid');
                selectBowler(playerId);
            });

            // ------------------------
            // 🔹 STORE RUNS & DELIVERIES
            // ------------------------
            let currentExtra = null;
            let currentWicket = null;

            // ------------------------
            // Add a delivery
            // ------------------------
            function addDelivery({
                runs = 0,
                extra = null,
                wicket = null,
                batsmanOut = null,
                legalBall = true
            }) {
                const state = JSON.parse(localStorage.getItem("match_state_" + matchId) || "{}");
                const strikerId = state?.striker?.id ?? null;
                const nonStrikerId = state?.nonStriker?.id ?? null;

                let bowlerId = state?.currentBowler ?? null;
                if (!bowlerId) {
                    const selectedBowlerInput = document.querySelector('input[name="current-bowler"]:checked');
                    bowlerId = selectedBowlerInput ? selectedBowlerInput.dataset.playerid : null;
                }

                if (!bowlerId) {
                    Swal.fire('Error', 'Please select the current bowler before recording delivery.', 'error');
                    return;
                }

                const payload = {
                    match_id: matchId,
                    striker_id: strikerId ? Number(strikerId) : null,
                    non_striker_id: nonStrikerId ? Number(nonStrikerId) : null,
                    bowler_id: Number(bowlerId),
                    runs: Number(runs ?? 0),
                    extras: extra ? extra : [],
                    wicket: wicket || null,
                    batsman_out: batsmanOut || null,
                    legal_ball: legalBall
                };

                console.dir(payload)

                sendDeliveryToServer(payload);
            }

            // ------------------------
            // Runs buttons
            // ------------------------
            document.querySelectorAll('.btn-run').forEach(btn => {
                btn.addEventListener('click', e => {
                    const run = parseInt(e.target.dataset.run);

                    addDelivery({
                        runs: run
                    });
                });
            });

            // ------------------------
            // Extras buttons
            // ------------------------
            document.querySelectorAll('.btn-extra').forEach(btn => {
                btn.addEventListener('click', () => {
                    const extraModal = document.getElementById('extraModal');
                    if (!extraModal) {
                        console.error("#extraModal not found in DOM");
                        return;
                    }

                    const modalTitle = extraModal.querySelector('.modal-title');
                    const nbSection = document.getElementById('nbSection');
                    const wdSection = document.getElementById('wdSection');
                    const lbSection = document.getElementById('lbSection');
                    const nbRunOutCheckbox = document.getElementById('nbRunOut');
                    const nbBatsmanOut = document.getElementById('nbBatsmanOut');
                    const type = btn.dataset.extra;

                    // 🔹 Reset sections
                    nbSection.classList.add('d-none');
                    wdSection.classList.add('d-none');
                    lbSection.classList.add('d-none');
                    modalTitle.textContent = "Extra";

                    if (type === "NB") {
                        modalTitle.textContent = "No Ball";
                        nbSection.classList.remove('d-none');

                        // Run Out toggle
                        nbRunOutCheckbox.addEventListener('change', function() {
                            if (this.checked) {
                                nbBatsmanOut.classList.remove('d-none');
                            } else {
                                nbBatsmanOut.classList.add('d-none');
                            }
                        });

                        loadCurrentPlayersToModal();
                    } else if (type === "WD") {
                        modalTitle.textContent = "Wide Ball";
                        wdSection.classList.remove('d-none');
                    } else if (type === "LB") {
                        modalTitle.textContent = "Leg Bye";
                        lbSection.classList.remove('d-none');
                    }
                });
            });

            // ------------------------
            // Extra modal: submit runs
            // ------------------------
            document.getElementById("extraForm").addEventListener("submit", function(e) {
                e.preventDefault(); // Prevent form submission

                // Determine which type of extra is selected
                let extra = null;
                let batsmanOut = null;

                // No Ball
                const nbSection = document.getElementById("nbSection");
                if (!nbSection.classList.contains("d-none")) {
                    const nbRun = document.querySelector('input[name="nbRuns"]:checked');
                    const runOutChecked = document.getElementById("nbRunOut").checked;
                    extra = {
                        type: "NB",
                        runs: nbRun ? Number(nbRun.value) : 0,
                        run_out: runOutChecked
                    };

                    if (runOutChecked) {
                        const selectedBatsman = nbSection.querySelector('input[name="player_id"]:checked');
                        if (selectedBatsman) batsmanOut = selectedBatsman.value;
                    }
                }

                // Wide Ball
                const wdSection = document.getElementById("wdSection");
                if (!wdSection.classList.contains("d-none")) {
                    const wdRun = document.querySelector('input[name="wdExtraRuns"]:checked');
                    extra = {
                        type: "WD",
                        runs: wdRun ? Number(wdRun.value) : 1,
                        run_out: false
                    };
                }

                // Leg Bye
                const lbSection = document.getElementById("lbSection");
                if (!lbSection.classList.contains("d-none")) {
                    const lbRun = document.querySelector('input[name="lbRuns"]:checked');
                    extra = {
                        type: "LB",
                        runs: lbRun ? Number(lbRun.value) : 0,
                        run_out: false
                    };
                }

                // Now call addDelivery with extra
                addDelivery({
                    runs: 0, // runs scored by batsman
                    extra: extra,
                    wicket: extra?.run_out ? "run_out" : null,
                    batsmanOut: batsmanOut,
                    legalBall: false // extras are generally illegal deliveries
                });

                // Close modal after submission
                const extraModal = bootstrap.Modal.getInstance(document.getElementById("extraModal"));
                extraModal.hide();
            });

            // ------------------------
            // Wicket buttons
            // ------------------------
            document.querySelectorAll('.btn-wicket').forEach(btn => {
                btn.addEventListener('click', e => {
                    const wicketType = e.target.dataset.wicket;
                    currentWicket = wicketType;

                    const modal = new bootstrap.Modal(document.getElementById('wicketModal'));

                    // Show run-out options only for Run Out
                    document.getElementById('runOutOptions').classList.toggle('d-none',
                        wicketType !== 'Run Out');

                    // Load players for caught/stumped
                    if (wicketType === 'Caught') loadBowlingTeamPlayers('caughtBySelect');
                    if (wicketType === 'Stumped') loadBowlingTeamPlayers('stumpedBySelect');

                    modal.show();
                });
            });

            document.querySelectorAll(".btn-wicket-type").forEach(btn => {
                btn.addEventListener("click", function() {
                    const type = this.dataset.wicket;

                    // Hide all extra sections first
                    document.querySelectorAll(".wicket-extra").forEach(el => el.classList.add(
                        "d-none"));

                    if (type === "Run Out") {
                        document.getElementById("runOutOptions").classList.remove("d-none");
                    } else if (type === "Caught") {
                        document.getElementById("caughtOptions").classList.remove("d-none");
                    } else if (type === "Stumped") {
                        document.getElementById("stumpedOptions").classList.remove("d-none");
                    } else {
                        finalizeWicket({
                            type: type,
                            batsmanOut: $('.btn-batsman-out.striker-btn').attr(
                                'data-batsman')
                        });
                    }
                });
            });

            // Run Out buttons
            document.querySelectorAll('.btn-batsman-out').forEach(btn => {
                btn.addEventListener('click', () => {
                    const batsmanOutId = Number(btn.dataset.batsman);
                    finalizeWicket({
                        type: 'Run Out',
                        batsmanOut: batsmanOut
                    });
                });
            });

            // Caught/Stumped finalization
            function finalizeCaughtOrStumped(type) {
                let batsmanOut = 'Striker';
                let fielderId = null;
                if (type === 'Caught') {
                    fielderId = document.getElementById('caughtBySelect').value;
                } else if (type === 'Stumped') {
                    fielderId = document.getElementById('stumpedBySelect').value;
                }

                finalizeWicket({
                    type: type,
                    batsmanOut: batsmanOut,
                    fielderId: fielderId
                });
            }

            function finalizeWicket({
                type,
                batsmanOut,
                fielderId = null
            }) {
                const wicketModalEl = document.getElementById('wicketModal');
                let modalInstance = bootstrap.Modal.getInstance(wicketModalEl);
                if (!modalInstance) {
                    modalInstance = new bootstrap.Modal(wicketModalEl);
                }

                modalInstance.hide();
                setTimeout(() => {
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    document.body.classList.remove('modal-open'); // remove modal-open class if stuck
                }, 100);

                let extra = null;
                if (type === 'Run Out') extra = {
                    run_out: true
                };
                else if (type === 'Caught') extra = {
                    caught_by: fielderId
                };
                else if (type === 'Stumped') extra = {
                    stumped_by: fielderId
                };

                // Add delivery
                addDelivery({
                    runs: 0,
                    extra: extra,
                    wicket: type,
                    batsmanOut: batsmanOut,
                    legalBall: true
                });
            }

            $('#btn-reset-wicket-form').on('click', function() {
                document.querySelectorAll(".btn-wicket-type").forEach(btn => {
                    btn.classList.remove("active");
                    btn.classList.remove("d-none"); // show them again if previously hidden
                });

                document.querySelectorAll(".wicket-extra").forEach(el => {
                    el.classList.add("d-none");
                });

                $('#wicketModal select').val('').trigger('change'); // reset dropdowns
                $('#wicketModal input').val(''); // reset inputs
                window.currentWicketData = null;
            });

            // Example function to populate dropdown from bowling team
            function loadBowlingTeamPlayers(selectId) {
                const select = document.getElementById(selectId);
                select.innerHTML = ""; // clear previous
                if (!bowlingTeamPlayers.length) {
                    select.innerHTML = `<option value="">No players available</option>`;
                    return;
                }
                bowlingTeamPlayers.forEach(player => {
                    const opt = document.createElement("option");
                    opt.value = player.id;
                    opt.textContent = player.name;
                    select.appendChild(opt);
                });
            }

            // ------------------------
            // Send payload to backend
            // ------------------------
            function sendDeliveryToServer(delivery) {
                fetch("{{ route('admin.cricket-matches.scoreboard.store-delivery') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(delivery)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) throw new Error(data.message || 'Failed to record delivery');

                        const saved = JSON.parse(localStorage.getItem("match_state_" + matchId) || "{}");
                        saved.striker = data.updated_state.striker;
                        saved.nonStriker = data.updated_state.nonStriker;
                        localStorage.setItem("match_state_" + matchId, JSON.stringify(saved));

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: data.message || 'Delivery stored & stats updated.',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true
                        }).then(() => {
                            loadCurrentStats(matchId);
                            loadCurrentOver();
                            window.location.reload();
                        });

                    })
                    .catch(err => console.error("Error recording delivery:", err));
            }

            function loadCurrentOver() {
                let chooseBowlerRoute =
                    "{{ route('admin.cricket-matches.scoreboard.current-over', ['match' => '__MATCH__']) }}";
                const url = chooseBowlerRoute.replace('__MATCH__', matchId);

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) return console.error(data.message);

                        const container = document.getElementById('currentOverDetails');
                        container.innerHTML = '';

                        data.balls.forEach(ball => {
                            const span = document.createElement('span');

                            // Add multiple classes safely
                            if (ball.class) {
                                ball.class.split(' ').forEach(cls => {
                                    if (cls.trim()) span.classList.add(cls.trim());
                                });
                            }

                            // Ensure ball.ball is a string
                            const ballLabel = String(ball.ball);

                            // Highlight wicket with red color
                            if (ballLabel.includes('W')) {
                                span.classList.add('wicket-ball'); // define in CSS
                            }

                            span.innerText = ballLabel;
                            container.appendChild(span);
                        });
                    })
                    .catch(err => console.error('Error loading current over:', err));
            }

            // ------------------------
            // 🔹 Load Yet-To-Bat Players
            // ------------------------
            function loadYetToBatPlayers() {
                fetch('/api/matches/yet-to-bat/' + matchId)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) return;

                        matchState.battingTeamId = data.battingTeamId;

                        const list = document.getElementById('yetToBatList');
                        list.innerHTML = '';

                        if (!data.players.length) {
                            list.innerHTML = '<li class="list-group-item text-muted">All players batted</li>';
                            return;
                        }

                        data.players.forEach(player => {
                            const card = document.createElement('div');
                            card.className = 'card mb-2 player-card';
                            card.dataset.playerId = player.id;
                            card.dataset.playerName = player.full_name;
                            card.innerHTML = `
                                <div class="align-items-center p-2 border flt-attribute" 
                                    style="display: flex;">
                                    <img src="${player.image}" alt="${player.full_name}" 
                                        class="rounded-circle me-3" width="48" height="48" 
                                        style="object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">${player.short_name}</h6>
                                        <small class="text-muted">${player.role}</small>
                                    </div>
                                    <button class="btn btn-sm btn-primary select-player-btn">Select</button>
                                </div>
                            `;
                            list.appendChild(card);
                        });
                    });
            }

            // ------------------------
            // 🔹 Select Players From Yet To Bat
            // ------------------------
            document.getElementById('yetToBatList').addEventListener('click', function(e) {
                if (!e.target.classList.contains('select-player-btn')) return;
                const card = e.target.closest('.player-card');
                const playerId = card.dataset.playerId;
                selectBatsman(playerId);
            });

            // ------------------------
            // 🔹 Search Filter
            // ------------------------
            $('#flt-player').on('input', function() {
                const searchTerm = this.value.toLowerCase();
                const cards = document.querySelectorAll('#yetToBatList .player-card');

                cards.forEach(card => {
                    const playerName = card.querySelector('.flt-attribute')
                        .getAttribute('data-player-name')
                        .toLowerCase();
                    card.style.display = playerName.includes(searchTerm) ? 'flex' : 'none';
                });
            });

            // ------------------------
            // Select Bowler
            // ------------------------
            const fetchBowlingTeamPlayers = () => {
                $.ajax({
                    url: `/admin/cricket-matches/scoreboard/${matchId}/team-b-players`,
                    type: "GET",
                    success: function(players) {
                        const options = players.map(p => ({
                            id: p.id,
                            text: `${p.name} - ${p.style}`
                        }));

                        $('#bowler-select').select2({
                            data: options,
                            placeholder: "Select a bowler",
                            width: "100%"
                        });
                    }
                });
            }

            // When a bowler is selected
            $('#bowler-select').on('select2:select', function(e) {
                const bowlerId = e.params.data.id;
                const teamId = $('#bowling_team_id').val();
                let chooseBowlerRoute =
                    "{{ route('admin.cricket-matches.scoreboard.choose-bowler', ['match' => '__MATCH__']) }}";
                const url = chooseBowlerRoute.replace('__MATCH__', matchId);
                $.ajax({
                    url: url,
                    type: "POST",
                    contentType: "application/json",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: JSON.stringify({
                        match_id: matchId,
                        bowler_id: bowlerId,
                        team_id: teamId
                    }),
                    success: function(res) {
                        if (res.success) {
                            const bowlingTbody = document.querySelector('#bowling-stats');
                            bowlingTbody.innerHTML = ""; // Clear all previous rows

                            // Append updated bowling stats
                            res.bowling.forEach(player => {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `
                                    <td>${player.name} - <small>${player.style}</small></td>
                                    <td class="text-center">${player.overs}</td>
                                    <td class="text-center">${player.maidens ?? 0}</td>
                                    <td class="text-center">${player.runs_conceded}</td>
                                    <td class="text-center">${player.wickets}</td>
                                    <td class="text-center">${player.economy_rate}</td>
                                `;
                                bowlingTbody.appendChild(tr);
                            });

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: "Bowler Selected..",
                                showConfirmButton: false,
                                timer: 2500,
                                timerProgressBar: true
                            });
                        } else {
                            alert(res.message || "Something went wrong!");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert("Error saving bowler.");
                    }
                });
            });

            function updateBowlingTable(bowlingList) {
                const tbody = document.querySelector('#bowling-stats');
                tbody.innerHTML = '';
                bowlingList.forEach(player => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${player.name}</td>
                        <td>${player.overs}</td>
                        <td>${player.runs_conceded}</td>
                        <td>${player.wickets}</td>
                        <td>${player.economy_rate}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            // ------------------------
            // Load Current Statistics
            // ------------------------
            function loadCurrentStats(matchId) {
                fetch("{{ route('admin.cricket-matches.scoreboard.load-current-stats') }}?match_id=" + matchId)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            console.log(data.match_result)
                            if (data.match_result) {
                                // Show alert repeatedly until dismissed
                                const showMatchResultAlert = () => {
                                    Swal.fire({
                                        title: `<span style="color:#155724;">🏆 ${data.match_result.winning_team}</span>`,
                                        html: `<h4 style="margin-top:10px;">${data.match_result.summary}</h4>`,
                                        icon: 'success',
                                        background: '#f0f9f4',
                                        color: '#155724',
                                        confirmButtonColor: '#198754',
                                        confirmButtonText: 'Celebrate 🎉',
                                        allowOutsideClick: false, // prevent closing by clicking outside
                                        allowEscapeKey: false, // prevent closing with ESC
                                        showClass: {
                                            popup: 'animate__animated animate__fadeInDown'
                                        },
                                        hideClass: {
                                            popup: 'animate__animated animate__fadeOutUp'
                                        }
                                    }).then(() => {
                                        // 💥 Confetti effect
                                        if (typeof confetti === "function") {
                                            confetti({
                                                particleCount: 200,
                                                spread: 100,
                                                origin: {
                                                    y: 0.6
                                                }
                                            });
                                        }
                                        // After closing, re-show the alert
                                        showMatchResultAlert();
                                    });
                                };

                                // Initial call
                                showMatchResultAlert();

                                return; // stop rendering stats if match already ended
                            }

                            const currentInnings = data.innings[data.innings.length - 1];
                            const batting = currentInnings.batting;
                            const bowling = currentInnings.bowling;
                            const scoreboard = currentInnings.scoreboard;
                            const partnerships = currentInnings.partnerships;
                            const fallOfWickets = currentInnings.fall_of_wickets;

                            if (data.isMatchEnded) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Match Ended',
                                    text: 'All innings are complete.',
                                    confirmButtonText: 'OK'
                                });
                                return;
                            }

                            if (scoreboard.target && scoreboard.target > 0) {
                                $('#targetScore').text(scoreboard.target);
                                $('#requiredRunRate').text(scoreboard.requiredRR);
                                $('.tagetscore-container').removeClass('d-none');
                                $('.requiredRunRate-container').removeClass('d-none');
                            } else {
                                $('.tagetscore-container').addClass('d-none');
                                $('.requiredRunRate-container').addClass('d-none');
                            }

                            $('input[name="innings"]').val(currentInnings.innings);
                            $('#bowling_team_id').val(currentInnings.bowling_team_id);

                            // ✅ Scoreboard update
                            $('#currentScore').text(scoreboard.runs + " / " + scoreboard.wickets);
                            $('#currentOvers').text(scoreboard.overs + " / " + scoreboard.totalOvers);
                            $('#currentCRR').text(scoreboard.currentCRR);
                            $('#projectedScore').text(scoreboard.projected);

                            // ✅ Batting table
                            const tbody = document.querySelector('#batting-stats');
                            tbody.innerHTML = '';
                            if (batting) {
                                batting.forEach(player => {
                                    const tr = document.createElement('tr');
                                    tr.innerHTML = `
                                        <td>${player.name}</td>
                                        <td class='text-center'>${player.runs}</td>
                                        <td class='text-center'>${player.balls}</td>
                                        <td class='text-center'>${player.fours}</td>
                                        <td class='text-center'>${player.sixes}</td>
                                        <td class='text-center'>${player.strike_rate}</td>
                                    `;
                                    tbody.appendChild(tr);
                                });
                            }

                            // ✅ Bowling table
                            const state = JSON.parse(localStorage.getItem("match_state_" + matchId) || "{}");
                            const bowlerId = state?.currentBowler ?? null;

                            const bowlingTbody = document.querySelector('#bowling-stats');
                            bowlingTbody.innerHTML = '';
                            if (bowling) {
                                bowling.forEach(player => {
                                    const tr = document.createElement('tr');
                                    tr.innerHTML = `
                                        <td style="vertical-align: middle;display: flex;align-items: center;">
                                            ${player.name}
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" value="${player.id}" data-playerid="${player.id}" name="current-bowler" style="margin-left: 10px;" ${bowlerId == player.id ? 'checked' : ''}>
                                            </div>
                                        </td>
                                        <td class='text-center'>${player.overs}</td>
                                        <td class='text-center'>${player.maidens ?? 0}</td>
                                        <td class='text-center'>${player.runs_conceded}</td>
                                        <td class='text-center'>${player.wickets}</td>
                                        <td class='text-center'>${player.economy_rate}</td>
                                    `;
                                    bowlingTbody.appendChild(tr);
                                });
                            }

                            // ✅ Bowler selection listener
                            bowlingTbody.addEventListener('change', (e) => {
                                if (e.target.name === 'current-bowler') {
                                    const currentBowlerId = e.target.value;
                                    saveMatchState();
                                }
                            });

                            // ✅ Partnerships
                            const partnershipList = document.querySelector('#partnership-stats');
                            partnershipList.innerHTML = '';
                            if (partnerships && partnerships.length > 0) {
                                partnerships.forEach(p => {
                                    let trContent = `<tr>
                                        <th>
                                            <div class="d-flex align-items-center p-2">
                                                <img src="${p.batter1.img || ''}" alt="${p.batter1.name}" class="rounded-circle me-3" width="48" height="48" style="object-fit: cover;">
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-2">${p.batter1.name}</h5>
                                                    <h6 class="text-muted">${p.batter1.role}</h6>
                                                </div>
                                            </div>
                                        </th>
                                        <td class="text-center">
                                            <div class="mb-1">
                                                <small>${p.runs} (${p.balls} balls)</small>
                                            </div>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar" role="progressbar" style="background: #F4991A!important;width: ${p.batter1.percent}%" aria-valuenow="${p.batter1.percent}" aria-valuemin="0" aria-valuemax="100"></div>
                                                <div class="progress-bar" role="progressbar" style="background: #84994F!important;width: ${p.batter2?.percent ?? 0}%" aria-valuenow="${p.batter2?.percent ?? 0}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <div class="d-flex align-items-center p-2">
                                                <div class="flex-grow-1 mr-2">
                                                    <h5 class="mb-2">${p.batter2?.name ?? ''}</h5>
                                                    <h6 class="text-muted">${p.batter2?.role ?? ''}</h6>
                                                </div>
                                                ${p.batter2 ? `<img src="${p.batter2.img || ''}" alt="${p.batter2.name}" class="rounded-circle" width="48" height="48" style="object-fit: cover; margin-left: 15px;">` : ''}
                                            </div>
                                        </td>
                                    </tr>`;
                                    partnershipList.innerHTML += trContent;
                                });
                            } else {
                                partnershipList.innerHTML = `<tr>
                                    <th colspan="3" class="text-center">Players Not Entered Yet</th>
                                </tr>`;
                            }

                            // ✅ Fall of wickets
                            const fallWicketsList = document.querySelector('#fallofwickets-stats');
                            fallWicketsList.innerHTML = '';
                            if (fallOfWickets && fallOfWickets.length > 0) {
                                fallOfWickets.forEach(w => {
                                    const tr = document.createElement('tr');
                                    tr.innerHTML = `
                                        <th>${w.player_name}</th>
                                        <td class="text-center">${w.runs}-${w.wicket_number}</td>
                                        <td class="text-center">${w.over}</td>
                                    `;
                                    fallWicketsList.appendChild(tr);
                                });
                            } else {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `<th colspan="3" class="text-center">No wickets fallen yet</th>`;
                                fallWicketsList.appendChild(tr);
                            }

                            loadMatchState();
                        } else {
                            console.error('Failed to load stats:', data.message);
                        }
                    })
                    .catch(err => console.error('Error fetching stats:', err));
            }


            $('.btn-complete-innings').on('click', function() {
                $.get("{{ route('admin.cricket-matches.scoreboard.mark-innings-complete') }}", {
                        match_id: matchId
                    })
                    .done(function(data) {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: data.message,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000
                            });

                            loadCurrentStats(matchId);
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .fail(function() {
                        Swal.fire('Error', 'Something went wrong', 'error');
                    });
            });

            // ------------------------
            // 🔹 Init
            // ------------------------
            loadYetToBatPlayers();
            loadMatchState();
            loadCurrentStats(matchId);
            fetchBowlingTeamPlayers();
            loadCurrentOver();
        });
    </script>
@endpush
