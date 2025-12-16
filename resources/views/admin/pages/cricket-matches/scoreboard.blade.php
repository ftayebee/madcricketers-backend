@extends('admin.layouts.theme')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link href="{{ asset('storage/backend/css/scoreboard.css') }}" rel="stylesheet">
@endpush

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <input type="hidden" name="is_toss_completed" value="{{ $match->toss ? 'true' : 'false' }}">
    <input type="hidden" name="toss_match_id" value="{{ $match->id }}">
    <div class="row">
        <div class="col-sm-12">
            @if (!$match->toss)
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-8">
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
                                    <div class="col-md-{{ $match->tournament && $match->tournament->logo ? '10' : '12' }}">
                                        <h4 class="fw-bolder mb-2 text-center text-purple match-title">
                                            {{ $match->teamA->name ?? 'Team A' }}
                                            <span class="text-muted">vs</span>
                                            {{ $match->teamB->name ?? 'Team B' }}
                                        </h4>

                                        <table class="table">
                                            <tr>
                                                <td width="8%" class="p-2 fs-16">Match Date</td>
                                                <td width="2%" class="p-2 fs-16">:</td>
                                                <td width="90%" class="p-2 fs-16">
                                                    {{ \Carbon\Carbon::parse($match->date)->format('d M, Y') ?? 'N/A' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="8%" class="p-2 fs-16">Tournament</td>
                                                <td width="2%" class="p-2 fs-16">:</td>
                                                <td width="90%" class="p-2 fs-16">
                                                    {{ $match->tournament->name ?? 'N/A' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="8%" class="p-2 fs-16">Venue</td>
                                                <td width="2%" class="p-2 fs-16">:</td>
                                                <td width="90%" class="p-2 fs-16">
                                                    {{ $match->venue ?? 'No Venue available.' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="8%" class="p-2 align-middle fs-16">Status</td>
                                                <td width="2%" class="p-2 align-middle fs-16">:</td>
                                                <td width="90%" class="p-2 fs-16">
                                                    <select name="start-match" id="start-match"
                                                        class="form-control select2 border-cyan">
                                                        <option value="live"
                                                            {{ $match->status == 'live' ? 'selected' : '' }}>Live</option>
                                                        <option value="completed"
                                                            {{ $match->status == 'completed' ? 'selected' : '' }}>Completed
                                                        </option>
                                                        <option value="upcoming"
                                                            {{ $match->status == 'upcoming' ? 'selected' : '' }}>Upcoming
                                                        </option>
                                                    </select>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 mb-4">
                                <div class="match-toss-container p-3 border rounded shadow-sm">
                                    <h4 class="text-center fw-bold py-2 mb-3 border-top border-bottom">
                                        Match Toss
                                    </h4>

                                    <!-- Team Selection -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Team</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <input type="radio" class="btn-check" name="toss-team" id="teamA"
                                                value="{{ $match->teamA->id }}"
                                                @if ($match->toss && $match->toss->toss_winner_team_id == $match->teamA->id) checked @endif>
                                            <label class="btn btn-outline-primary flex-fill"
                                                for="teamA">{{ $match->teamA->name }}</label>

                                            <input type="radio" class="btn-check" name="toss-team" id="teamB"
                                                value="{{ $match->teamB->id }}"
                                                @if ($match->toss && $match->toss->toss_winner_team_id == $match->teamB->id) checked @endif>
                                            <label class="btn btn-outline-primary flex-fill"
                                                for="teamB">{{ $match->teamB->name }}</label>
                                        </div>
                                    </div>

                                    <!-- Decision Selection -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Decision</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <input type="radio" class="btn-check" name="toss-decision" id="bat"
                                                value="BAT" @if ($match->toss && $match->toss->decision == 'bat') checked @endif>
                                            <label class="btn btn-outline-success flex-fill" for="bat">BAT</label>

                                            <input type="radio" class="btn-check" name="toss-decision" id="bowl"
                                                value="BOWL" @if ($match->toss && $match->toss->decision == 'bowl') checked @endif>
                                            <label class="btn btn-outline-success flex-fill" for="bowl">BOWL</label>
                                        </div>
                                    </div>

                                    <button class="btn btn-info w-100" id="btn-save-toss">Save Toss</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endif
        </div>
        @if (!empty($match->scoreboard->toArray()))
            <div class="col-12 col-md-8 match-scoreboard">
                <input type="hidden" name="innings"
                    value="{{ $match->scoreboard->where('status', 'running')->first()->innings ?? 0 }}">
                <input type="hidden" name="max_overs" value="{{ $match->max_overs }}">
                <input type="hidden" id="bowling_team_id"
                    value="{{ $match->scoreboard->whereIn('status', ['waiting', 'ended'])->first()->team_id }}">
                <input type="hidden" name="battingTeamId"
                    value="{{ $match->scoreboard->where('status', 'running')->first()->team_id ?? 0 }}">

                <div class="card" id="match-scoreboard" class="d-block">
                    <div class="card-body p-3">
                        <div class="row align-items-center g-3">
                            <!-- LEFT: Team & Score -->
                            <div class="col-12 col-md-9">
                                <div class="d-flex align-items-start align-items-md-center gap-3">
                                    <!-- Team Info -->
                                    <div class="flex-grow-1 text-center">
                                        <h4 id="battingTeamName" class="fw-bold text-primary mb-2 fs-md-4 text-center ">
                                            Team A
                                        </h4>
                                        <!-- Score Line -->
                                        <div
                                            class="d-flex flex-wrap gap-2 small fw-semibold score-info justify-content-between">
                                            <span id="currentScore" class="badge bg-dark d-flex align-items-center">
                                                0 / 0
                                            </span>
                                            <span>
                                                <strong>O:</strong>
                                                <span id="currentOvers">(0.0)</span>
                                            </span>
                                            <span>
                                                <strong>CRR:</strong>
                                                <span id="currentCRR">0.0</span>
                                            </span>
                                            <span class="projectedScore-container d-none">
                                                <strong>Proj:</strong>
                                                <span id="projectedScore">00</span>
                                            </span>
                                            <span class="tagetscore-container d-none">
                                                <strong>Target:</strong>
                                                <span id="targetScore">00</span>
                                            </span>
                                            <span class="requiredRunRate-container d-none">
                                                <strong>RR:</strong>
                                                <span id="requiredRunRate">00</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- RIGHT: Actions -->
                            <div class="col-12 col-md-3">
                                <div class="d-flex flex-wrap gap-2 justify-content-center">
                                    <button class="btn btn-outline-danger btn-complete-innings w-md-auto"
                                        style="width: 48% !important">
                                        <span class="d-none d-sm-inline">Complete Innings</span>
                                        <span class="d-inline d-sm-none">End Inn.</span>
                                    </button>

                                    <button class="btn btn-outline-secondary btn-undo-ball w-md-auto"
                                        style="width: 48% !important">
                                        <span class="d-none d-sm-inline">Undo Ball</span>
                                        <span class="d-inline d-sm-none">Undo</span>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-12">
                        <div class="card" id="match-scoreboard" class="d-block">
                            <div class="card-header">
                                <h4 class="fs-5 fw-bold mb-0">Current Batsman</h4>
                            </div>
                            <div class="card-body row">
                                <div class="col-12 col-sm-6">
                                    <div class="card mb-2 border-info border-1">
                                        <div class="card-body d-flex align-items-center gap-2 p-2">
                                            <img src="https://img.freepik.com/free-vector/illustration-gallery-icon_53876-27002.jpg?semt=ais_hybrid&w=740&q=80"
                                                alt="Player" class="rounded-circle" width="42" height="42">

                                            <div class="flex-grow-1">
                                                <h6 class="mb-0 fs-14" id="strikerName">Choose Player</h6>
                                            </div>

                                            <div>
                                                <p class="text-black mb-1 fs-12 text-right">
                                                    <span id="strikerRuns">00</span>
                                                    (<span id="strikerBallsFaced">0</span> balls)
                                                </p>
                                                <p class="text-muted mb-0 fs-12 text-right">
                                                    SR: <span id="strikerStrikeRate">0.00</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6">
                                    <div class="card mb-2 border-info border-1">
                                        <div class="card-body d-flex align-items-center gap-2 p-2">
                                            <img src="https://img.freepik.com/free-vector/illustration-gallery-icon_53876-27002.jpg?semt=ais_hybrid&w=740&q=80"
                                                alt="Player" class="rounded-circle" width="42" height="42">

                                            <div class="flex-grow-1">
                                                <h6 class="mb-0 fs-14" id="nonStrikerName">Choose Player</h6>
                                            </div>

                                            <div>
                                                <p class="text-black mb-1 fs-12 text-right">
                                                    <span id="nonStrikerRuns">00</span>
                                                    (<span id="nonStrikerBallsFaced">0</span> balls)
                                                </p>
                                                <p class="text-muted mb-0 fs-12 text-right">
                                                    SR: <span id="nonStrikerStrikeRate">0.00</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card" class="d-block">
                            <div class="card-header">
                                <h4 class="fs-5 fw-bold mb-0">Add Delivery</h4>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-3">
                                    <div>
                                        <h5>Runs</h5>
                                        @foreach (['0', '1', '2', '3', '4', '6'] as $run)
                                            <button class="btn btn-outline-primary btn-run"
                                                data-run="{{ $run }}">{{ $run }}</button>
                                        @endforeach
                                    </div>

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

                                    <div>
                                        <h5>Wickets</h5>
                                        <button class="btn btn-outline-danger btn-wicket" data-bs-toggle="modal"
                                            data-bs-target="#wicketModal" data-type="W">W</button>
                                    </div>
                                </div>

                                <div class="mt-3 fs-16">
                                    <p class="mb-2"><strong>Current Over:</strong></p>
                                    <div id="currentOverDetails" class="over-display" style="margin-left: 15px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card" id="match-scoreboard" class="d-block">
                            <div class="card-header">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h4 class="fs-5 fw-bold mb-0">Current Bowler</h4>

                                    <button class="btn btn-outline-info" id="btn-trigger-bowler-modal">
                                        Change Bowler
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div
                                    class="fs-16 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                                    <div
                                        class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2">
                                        <div id="currentBowlerDetails" class="ms-0 ms-sm-2">
                                            Choose Bowler
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4 match-scoreboard">
                <div class="card">
                    <div class="card-body">
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

            <div class="col-12 col-md-12 d-none" id="match-result">
                <div class="card" style="padding: auto 90px; background: transparent;">
                    <div class="card-body">
                        <div class="winner-wrap" id="winner-wrap">
                            <div class="border"></div>
                            <div class="medal-box"><i class="fas fa-medal"></i></div>
                            <h1 id="match-title">Ocean Race Challenge 2019</h1>
                            <h2 id="match-result_summary">Team Alpha</h2>
                            <div class="winner-ribbon">WINNER</div>
                            <div class="right-ribbon"></div>
                            <div class="left-ribbon"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="modal fade" id="bowlerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Bowler</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="text" class="form-control mb-3" id="bowlerSearch" placeholder="Search bowler">

                    <div id="bowlerList" class="list-group">

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="extraModal" tabindex="-1" aria-labelledby="extraModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="extraForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="extraModalLabel">Extra</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        {{-- NB Section --}}
                        <div id="nbSection" class="d-none">
                            <p><strong>No Ball:</strong> Select runs + run out option</p>
                            <div>
                                <label>Runs:</label>
                                <div class="btn-group" role="group" id="extraRunButtons">
                                    @foreach ([0, 1, 2, 3, 4, 6] as $r)
                                        <input type="radio" class="btn-check" name="nbRuns" id="nbRun{{ $r }}" value="{{ $r }}">
                                        <label class="btn btn-outline-primary"
                                            for="nbRun{{ $r }}">{{ $r }}</label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="mt-3">
                                <input type="checkbox" id="nbRunOut" class="form-check-input">
                                <label for="nbRunOut" class="form-check-label">Run Out?</label>
                            </div>
                            <div id="nbBatsmanOut" class="mt-2 d-none">
                                <label class="fs-14" style="font-weight: bold;">Choose Batsman (Run Out):</label>
                                <div class="player-wrapper row mt-2">
                                    <!-- Striker Card -->
                                    <div class="col-12 col-sm-6">
                                        <input type="radio" class="d-none player-radio" name="player_id"
                                            id="player_striker" value="{{ $strikerId ?? '' }}">
                                        <label for="player_striker" class="player-card-label mb-2">
                                            <div class="card border-info border-1 player-card">
                                                <div class="card-body d-flex align-items-center gap-2 p-2">
                                                    <img src="https://img.freepik.com/free-vector/illustration-gallery-icon_53876-27002.jpg?semt=ais_hybrid&w=740&q=80"
                                                        alt="Player" class="rounded-circle" width="42"
                                                        height="42">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-0 fs-14" id="mdl-strikerName">Choose Player</h6>
                                                        <small class="text-muted">Striker</small>
                                                    </div>
                                                    <div class="player-check ms-2 d-none">
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Non-Striker Card -->
                                    <div class="col-12 col-sm-6">
                                        <input type="radio" class="d-none player-radio" name="player_id"
                                            id="player_nonstriker" value="{{ $nonStrikerId ?? '' }}">
                                        <label for="player_nonstriker" class="player-card-label mb-2">
                                            <div class="card border-info border-1 player-card">
                                                <div class="card-body d-flex align-items-center gap-2 p-2">
                                                    <img src="https://img.freepik.com/free-vector/illustration-gallery-icon_53876-27002.jpg?semt=ais_hybrid&w=740&q=80"
                                                        alt="Player" class="rounded-circle" width="42"
                                                        height="42">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-0 fs-14" id="mdl-nonStrikerName">Choose Player</h6>
                                                        <small class="text-muted">Non-Striker</small>
                                                    </div>
                                                    <div class="player-check ms-2 d-none">
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="text-danger fs-12 mt-1" id="playerError"></div>
                            </div>
                        </div>

                        {{-- WD Section --}}
                        <div id="wdSection" class="d-none">
                            <p><strong>Wide Ball:</strong> Default 1 run counted. Add extras if needed.</p>
                            <div>
                                <label>Extra Runs:</label>
                                <div class="btn-group" role="group">
                                    @foreach ([0, 1, 2, 3, 4, 5, 6] as $r)
                                        <input type="radio" class="btn-check" name="wdExtraRuns"
                                            id="wdRun{{ $r }}" value="{{ $r }}">
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
                                        <input type="radio" class="btn-check" name="lbRuns"
                                            id="lbRun{{ $r }}" value="{{ $r }}">
                                        <label class="btn btn-outline-primary"
                                            for="lbRun{{ $r }}">{{ $r }}</label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                        <button class="btn btn-outline-danger btn-wicket-type" data-wicket="LBW">LBW</button>
                        <button class="btn btn-outline-danger btn-wicket-type"
                            data-wicket="Retired-Hurt">Retired-Hurt</button>
                        <button class="btn btn-outline-danger btn-wicket-type"
                            data-wicket="Hit-Wicket">Hit-Wicket</button>
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
                        <select class="form-select" id="caughtBySelect"></select>
                    </div>

                    <!-- Stumped Options -->
                    <div id="stumpedOptions" class="mt-3 d-none wicket-extra">
                        <p>Select Keeper (Who stumped):</p>
                        <select class="form-select" id="stumpedBySelect"></select>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="choosePlayersModal" tabindex="-1" aria-labelledby="choosePlayersModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="choosePlayersModalLabel">Select Players</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="selectStriker" class="form-label">Striker</label>
                        <select id="selectStriker" class="form-select">
                            <!-- Options populated dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="selectNonStriker" class="form-label">Non-Striker</label>
                        <select id="selectNonStriker" class="form-select">
                            <!-- Options populated dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="selectBowler" class="form-label">Bowler</label>
                        <select id="selectBowler" class="form-select">
                            <!-- Options populated dynamically -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="savePlayersBtn">Save</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('storage/backend/js/scoreboard.js') }}"></script>
@endpush
