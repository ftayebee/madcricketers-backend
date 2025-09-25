@extends('admin.layouts.theme')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link href="{{asset('storage/backend/css/scoreboard.css')}}" rel="stylesheet">
@endpush

@section('content')  

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
                                    <h4 class="fw-bolder mb-2 fs-28 text-center text-cyan">
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
                                            <td width="90%" class="p-2 fs-16">{{ $match->tournament->name ?? 'N/A' }}
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
                                                <select name="start-match" id="start-match" class="form-control select2 w-25 border-cyan">
                                                    <option value="live" {{ $match->status == 'live' ? 'selected' : '' }}>Live</option>
                                                    <option value="completed" {{ $match->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                                    <option value="upcoming" {{ $match->status == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-4 text-center match-toss-container">
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
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="team-details">
                                        <div class="team-icon">
                                            {{-- circular shape with team name first letters like Thunder Warriors will show TW or Mountain Kings will show MK or Crackers with show CR --}}
                                        </div>
                                        <div class="team-info">
                                            <h4 class="mb-1 fw-bold text-primary fs-28" id="battingTeamName">Team A</h4>
                                            <div class="score-info d-flex flex-wrap gap-3 fs-24">
                                                <div class="currentScore-container">
                                                    <span id="currentScore" class="">0 / 0</span>
                                                </div>

                                                <div class="currentOvers-container">
                                                    <strong>Overs:</strong>
                                                    <span id="currentOvers" class="">(0.0)</span>
                                                </div>
                                                <div class="currentCRR-container">
                                                    <strong>CRR:</strong>
                                                    <span id="currentCRR" class="">0.0</span>
                                                </div>
                                                <div class="projectedScore-container d-none">
                                                    <strong>Projected:</strong>
                                                    <span id="projectedScore" class="">00</span>
                                                </div>

                                                <div class="tagetscore-container d-none">
                                                    <strong>Target:</strong>
                                                    <span id="targetScore">00</span>
                                                </div>

                                                <div class="requiredRunRate-container d-none">
                                                    <strong>RR:</strong>
                                                    <span id="requiredRunRate">00</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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

            <div class="card" id="match-result" class="d-none" style="padding: auto 90px; background: transparent;">
                <div class="card-body" >
                    <div class="winner-wrap" id="winner-wrap">
                        <div class="border"></div>
                        <div class="medal-box"><i class="fas fa-medal"></i></div>
                        <h1>Ocean Race Challenge 2019</h1>
                        <h2>Team Alpha</h2>
                        <div class="winner-ribbon">WINNER</div>
                        <div class="right-ribbon"></div>
                        <div class="left-ribbon"></div>
                    </div>
                </div>
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
    <script src="{{asset('storage/backend/js/scoreboard.js')}}"></script>
@endpush
