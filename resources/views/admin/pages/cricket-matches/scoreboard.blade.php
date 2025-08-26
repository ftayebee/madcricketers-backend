@extends('admin.layouts.theme')

@section('content')
    @push('styles')
        <style>
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

            .boundary-ball {
                background-color: #ff9800;
            }

            .six-ball {
                background-color: #e91e63;
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

                                        <span
                                            class="badge bg-{{ $match->status == 'completed' ? 'success' : ($match->status == 'live' ? 'danger' : 'secondary') }}">
                                            {{ ucfirst($match->status) }}
                                        </span>
                                    </h4>

                                    <table class="table">
                                        <tr>
                                            <td width="8%" class="p-2">Match Date</td>
                                            <td width="2%" class="p-2">:</td>
                                            <td width="90%" class="p-2">
                                                {{ \Carbon\Carbon::parse($match->date)->format('d M, Y') ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td width="8%" class="p-2">Tournament</td>
                                            <td width="2%" class="p-2">:</td>
                                            <td width="90%" class="p-2">{{ $match->tournament->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td width="8%" class="p-2">Venue</td>
                                            <td width="2%" class="p-2">:</td>
                                            <td width="90%" class="p-2">{{ $match->venue ?? 'No Venue available.' }}
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
                                            <input type="hidden" name="max_overs" value="{{$match->max_overs}}">
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

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <span id="battingTeamName">Team A</span>
                        </h5>
                        <small>
                            Score: <span id="currentScore">0 / 0</span> |
                            Overs: <span id="currentOvers">0.0</span> |
                            CRR: <span id="currentCRR">0.0</span> |
                            Projected: <span id="projectedScore">00</span>
                        </small>
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
                                                <p class="m-0">Runs: <span id="strikerRuns">00</span> (0 balls)</p>
                                            </div>
                                        </div>
                                        <div id="strikerActions">
                                            {{-- <button class="btn btn-danger btn-sm"
                                                onclick="markAsOut('striker')">Out</button> --}}
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
                                            <p class="m-0">Runs: <span id="nonStrikerRuns">00</span> (0 balls)</p>
                                        </div>
                                        <div class="d-flex flex-column" id="nonStrikerActions">
                                            {{-- <button class="btn btn-danger btn-sm mb-1"
                                                onclick="markAsOut('nonStriker')">Out</button> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Scoring buttons --}}
                            <div class="col-12">
                                <div class="d-flex flex-wrap gap-3 mt-3">

                                    {{-- Runs --}}
                                    <div>
                                        <h5>Runs</h5>
                                        @foreach (['0', '1', '2', '3', '4', '6'] as $run)
                                            <button class="btn btn-outline-primary"
                                                onclick="recordRun('{{ $run }}')">{{ $run }}</button>
                                        @endforeach
                                    </div>

                                    {{-- Extras --}}
                                    <div>
                                        <h5>Extras</h5>
                                        @foreach (['NB', 'WD', 'LB1', 'LB2', 'LB3'] as $extra)
                                            <button class="btn btn-outline-warning"
                                                onclick="recordRun('{{ $extra }}')">{{ $extra }}</button>
                                        @endforeach

                                        <div class="modal fade" id="extraModal" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Extra Runs</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Runs scored on this extra:</p>
                                                        <div id="extraRunButtons" class="d-flex gap-2">
                                                            <button class="btn btn-outline-primary"
                                                                onclick="submitExtraRun(0)">0</button>
                                                            <button class="btn btn-outline-primary"
                                                                onclick="submitExtraRun(1)">1</button>
                                                            <button class="btn btn-outline-primary"
                                                                onclick="submitExtraRun(2)">2</button>
                                                            <button class="btn btn-outline-primary"
                                                                onclick="submitExtraRun(3)">3</button>
                                                            <button class="btn btn-outline-primary"
                                                                onclick="submitExtraRun(4)">4</button>
                                                            <button class="btn btn-outline-primary"
                                                                onclick="submitExtraRun(6)">6</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Wickets --}}
                                    <div>
                                        <h5>Wickets</h5>
                                        @foreach (['W', 'Run Out', 'Bowled', 'Caught', 'LBW', 'Stumped'] as $wicket)
                                            <button class="btn btn-outline-danger"
                                                onclick="recordWicket('{{ $wicket }}')">{{ $wicket }}</button>
                                        @endforeach
                                    </div>

                                </div>

                                {{-- Over display --}}
                                <div class="mt-3 fs-16 d-flex align-items-center">
                                    <p class="m-0"><strong>Current Over:</strong></p>
                                    <div id="currentOverDetails" class="over-display" style="margin-left: 15px;">
                                        {{-- <span class="ball run-ball">1</span>
                                        <span class="ball dot-ball">0</span>
                                        <span class="ball boundary-ball">4</span>
                                        <span class="ball run-ball">2</span>
                                        <span class="ball run-ball">1</span>
                                        <span class="ball dot-ball">0</span> --}}
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
                                        <th width="50%" class="text-left">Batter</th>
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
                            <h4 style="font-weight: bold; border-radius: 5px;" class="bg-soft-info py-2 text-uppercase">
                                Bowling</h4>

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th width="50%" class="text-left">Bowler</th>
                                        <th width="10%" class="text-center">O</th>
                                        <th width="10%" class="text-center">M</th>
                                        <th width="10%" class="text-center">R</th>
                                        <th width="10%" class="text-center">W</th>
                                        <th width="10%" class="text-center">ER</th>
                                    </tr>
                                </thead>
                                <tbody id="bowling-stats">
                                    <tr id="add-bowler-row">
                                        <td colspan="1">
                                            <select id="bowler-select" style="width: 300px;">
                                                <option value="">Select Bowler</option>
                                            </select>
                                        </td>
                                        <td colspan="5">
                                        </td>
                                    </tr>
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

                    {{-- Right Sidebar: Yet to Bat --}}
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
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let selectedTeam = null;
            let selectedDecision = null;
            let matchId = $('input[name="toss_match_id"]').val();

            let strikerId = null;
            let nonStrikerId = null;

            function switchStrike() {
                if (!strikerId || !nonStrikerId) return;

                // Swap IDs
                [strikerId, nonStrikerId] = [nonStrikerId, strikerId];
                // Swap Names
                let strikerNameEl = document.getElementById("strikerName");
                let strikerImgEl = document.getElementById("strikerImg");
                let nonStrikerNameEl = document.getElementById("nonStrikerName");
                let nonStrikerImgEl = document.getElementById("nonStrikerImg");

                // Swap text and images
                [strikerNameEl.innerText, nonStrikerNameEl.innerText] = [nonStrikerNameEl.innerText, strikerNameEl
                    .innerText
                ];
                [strikerImgEl.src, nonStrikerImgEl.src] = [nonStrikerImgEl.src, strikerImgEl.src];

                // Update strike buttons
                updateStrikeButtons('striker');

                // Update database
                fetch("{{ route('admin.cricket-matches.scoreboard.switch-strike') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            match_id: matchId,
                            striker_id: strikerId,
                            non_striker_id: nonStrikerId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: data.message || 'Failed to switch strike',
                                showConfirmButton: false,
                                timer: 2500,
                                timerProgressBar: true
                            });
                        }
                    })
                    .catch(err => console.error("Error switching strike:", err));
            }

            function updateStrikeButtons() {
                // Remove any existing "On Strike" buttons
                document.querySelectorAll('.on-strike-btn').forEach(btn => btn.remove());

                // Create new "On Strike" button
                const btn = document.createElement('button');
                btn.classList.add('btn', 'btn-info', 'btn-sm', 'on-strike-btn');
                btn.innerText = 'On Strike';
                btn.onclick = () => switchStrike();

                // Always append to non-striker actions container
                const nonStrikerContainer = document.getElementById('nonStrikerActions');
                if (nonStrikerContainer) {
                    nonStrikerContainer.appendChild(btn);
                }
            }

            // ------------------------
            // 🔹 Save match state to localStorage
            // ------------------------
            function saveMatchState() {
                const state = {
                    striker: strikerId ? {
                        id: strikerId,
                        name: document.getElementById("strikerName")?.innerText,
                        img: document.getElementById("strikerImg")?.src
                    } : null,

                    nonStriker: nonStrikerId ? {
                        id: nonStrikerId,
                        name: document.getElementById("nonStrikerName")?.innerText,
                        img: document.getElementById("nonStrikerImg")?.src
                    } : null,

                    team: {
                        name: document.getElementById("battingTeamName")?.innerText,
                        score: document.getElementById("currentScore")?.innerText,
                        overs: document.getElementById("currentOvers")?.innerText,
                        crr: document.getElementById("currentCRR")?.innerText,
                        projected: document.getElementById("projectedScore")?.innerText
                    }
                };

                localStorage.setItem("match_state_" + matchId, JSON.stringify(state));
            }

            // ------------------------
            // 🔹 Load saved state from localStorage
            // ------------------------
            function loadMatchState() {
                const saved = localStorage.getItem("match_state_" + matchId);

                if (!saved) {
                    // Fetch from backend if not in localStorage
                    fetch("{{ route('admin.cricket-matches.scoreboard.match-info', ['match_id' => '']) }}" +
                            matchId, {
                                method: "GET",
                            })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                localStorage.setItem("match_state_" + matchId, JSON.stringify(data
                                    .match_state));
                                populateMatchState(data.match_state);
                            } else {
                                console.error("Failed to load match state:", data.message);
                            }
                        })
                        .catch(err => console.error("Error fetching match state:", err));
                } else {
                    const state = JSON.parse(saved);
                    populateMatchState(state);
                }
            }

            function populateMatchState(state) {
                if (state.striker) {
                    strikerId = state.striker.id;
                    document.getElementById("strikerName").innerText = state.striker.name;
                    // document.getElementById("strikerImg").src = state.striker.img;
                    updateStrikeButtons('striker'); // ✅ add button next to striker
                }

                if (state.nonStriker) {
                    nonStrikerId = state.nonStriker.id;
                    document.getElementById("nonStrikerName").innerText = state.nonStriker.name;
                    // document.getElementById("nonStrikerImg").src = state.nonStriker.img;
                    // you don't need updateStrikeButtons for non-striker unless you want to highlight swap
                }

                if (state.team) {
                    document.getElementById("battingTeamName").innerText = state.team.name;
                    document.getElementById("currentScore").innerText = state.team.score;
                    document.getElementById("currentOvers").innerText = state.team.overs;
                    document.getElementById("currentCRR").innerText = state.team.crr;
                    document.getElementById("projectedScore").innerText = state.team.projected;
                }
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
                const playerCard = document.querySelector(`[data-playerid="${playerId}"]`);
                if (!playerCard) return;

                const playerName = playerCard.getAttribute("data-player-name");
                const playerImg = playerCard.querySelector("img").src;
                const teamId = $('input[name="battingTeamId"]').val();

                if (!strikerId) {
                    saveBatsman(matchId, teamId, playerId, "on-strike", () => {
                        saveMatchState();
                        strikerId = playerId;
                        document.getElementById("strikerName").innerText = playerName;
                        document.getElementById("strikerImg").src = playerImg;

                        playerCard.closest(".player-card").remove();
                    });
                    return;
                }

                if (!nonStrikerId) {

                    saveBatsman(matchId, teamId, playerId, "batting", () => {
                        saveMatchState();
                        nonStrikerId = playerId;
                        document.getElementById("nonStrikerName").innerText = playerName;
                        document.getElementById("nonStrikerImg").src = playerImg;

                        playerCard.closest(".player-card").remove();
                    });
                    return;
                }

                alert("Both striker and non-striker are already selected.");
            };

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
                            console.log("Player saved:", data.data);

                            if (callback) callback(); // only now save to localStorage
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
            // 🔹 STORE RUNS
            // ------------------------
            let currentExtra = null;

            window.recordRun = function(value) {
                // Check if extra requires modal
                if (['NB', 'WD'].includes(value)) {
                    currentExtra = value;
                    const modal = new bootstrap.Modal(document.getElementById('extraModal'));
                    modal.show();
                    return; // wait for user to select runs
                }

                // Determine runs & extra type for normal/leg byes
                let runs = 0;
                let extraType = null;
                let legalBall = true;

                if (['0', '1', '2', '3', '4', '6'].includes(value)) {
                    runs = parseInt(value);
                } else if (value.startsWith('LB')) {
                    extraType = 'LB';
                    runs = parseInt(value.replace('LB', '')); // LB1, LB2, etc.
                }

                storeBall({
                    runs,
                    extraType,
                    legalBall
                });
            }

            // Called from modal for NB/WD
            window.submitExtraRun = function(runsScored) {
                let runs = runsScored;
                let extraType = currentExtra; // NB or WD
                let legalBall = false; // NB/WD do not count toward over

                storeBall({
                    runs,
                    extraType,
                    legalBall
                });

                const modal = bootstrap.Modal.getInstance(document.getElementById('extraModal'));
                modal.hide();
                currentExtra = null;
            }

            // Store ball to server
            function storeBall({
                runs,
                extraType,
                legalBall
            }) {
                fetch("{{ route('admin.cricket-matches.scoreboard.store-runs') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            match_id: matchId,
                            striker_id: strikerId,
                            non_striker_id: nonStrikerId,
                            runs: runs,
                            extra_type: extraType,
                            legal_ball: legalBall
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            updateScoreUI(data.updated_state);
                        } else {
                            Swal.fire('Error', data.message || 'Failed to record run', 'error');
                        }
                    })
                    .catch(err => console.error("Error recording run:", err));
            }


            // ------------------------
            // 🔹 Mark Player as Out
            // ------------------------
            window.markAsOut = function(role) {
                if (role === 'striker') {
                    strikerId = null;
                    document.getElementById("strikerName").innerText = "Choose Player";
                    document.getElementById("strikerImg").src = "";
                } else {
                    nonStrikerId = null;
                    document.getElementById("nonStrikerName").innerText = "Choose Player";
                    document.getElementById("nonStrikerImg").src = "";
                }
                saveMatchState();
            };

            // ------------------------
            // 🔹 Load Yet-To-Bat Players
            // ------------------------
            function loadYetToBatPlayers() {
                fetch('/api/matches/yet-to-bat/' + matchId)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) return;

                        const list = document.getElementById('yetToBatList');
                        list.innerHTML = '';
                        $('input[name="battingTeamId"]').val(data.battingTeamId);

                        if (data.players.length) {
                            data.players.forEach(player => {
                                const card = document.createElement('div');
                                card.className = 'card mb-2 player-card';

                                card.innerHTML = `
                                    <div class="align-items-center p-2 border flt-attribute" 
                                        data-player-name="${player.full_name}" 
                                        style="display: flex;" 
                                        data-playerid="${player.id}">
                                        <img src="${player.image}" alt="${player.full_name}" 
                                            class="rounded-circle me-3" width="48" height="48" 
                                            style="object-fit: cover;">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">${player.short_name}</h6>
                                            <small class="text-muted">${player.role}</small>
                                        </div>
                                        <button class="btn btn-sm btn-primary" onclick="selectBatsman('${player.id}')">Select to Bat</button>
                                    </div>
                                `;
                                list.appendChild(card);
                            });
                        } else {
                            list.innerHTML = '<li class="list-group-item text-muted">All players batted</li>';
                        }
                    });
            }

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
                    success: function (players) {
                        const options = players.map(p => ({
                            id: p.id,
                            text: `${p.name} - ${p.style}`
                        }));

                        // Initialize Select2
                        $('#bowler-select').select2({
                            data: options,
                            placeholder: "Select a bowler",
                            width: "100%"
                        });
                    }
                });
            }

            // When a bowler is selected
            $('#bowler-select').on('select2:select', function (e) {
                const bowlerId = e.params.data.id;
                const teamId = $('#bowling_team_id').val();
                let chooseBowlerRoute = "{{ route('admin.cricket-matches.scoreboard.choose-bowler', ['match' => '__MATCH__']) }}";
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
                    success: function (res) {
                        if (res.success) {
                            const bowlingTbody = document.querySelector('#bowling-stats');
                            const rows = bowlingTbody.querySelectorAll('tr');
                            rows.forEach((row, index) => { if (index > 0) row.remove(); });

                            // Append updated bowling stats
                            res.bowling.forEach(player => {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `
                                    <td>${player.name} - <small>${player.style}</small></td>
                                    <td class="text-center">${player.overs}</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">${player.runs_conceded}</td>
                                    <td class="text-center">${player.wickets}</td>
                                    <td class="text-center">${player.economy_rate}</td>
                                `;
                                bowlingTbody.appendChild(tr);
                            });
                            alert("Bowler Selected"); // getting alert
                        } else {
                            alert(res.message || "Something went wrong!");
                        }
                    },
                    error: function (xhr) {
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
            // Load Batting Player List
            // ------------------------
            function loadCurrentStats(matchId) {
                fetch("{{ route('admin.cricket-matches.scoreboard.load-current-stats') }}?match_id=" + matchId)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            console.log(data)
                            $('input[name="innings"]').val(data.innings);
                            $('#bowling_team_id').val(data.bowling_team_id);

                            const tbody = document.querySelector('#batting-stats');
                            tbody.innerHTML = '';

                            data.batting.forEach(player => {
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

                            // 🏏 Update bowling table
                            // const bowlingTbody = document.querySelector('#bowling-stats');
                            // bowlingTbody.innerHTML = '';
                            // data.bowling.forEach(player => {
                            //     const tr = document.createElement('tr');
                            //     tr.innerHTML = `
                            //         <td>${player.name}</td>
                            //         <td>${player.overs}</td>
                            //         <td>${player.runs_conceded}</td>
                            //         <td>${player.wickets}</td>
                            //         <td>${player.economy_rate}</td>
                            //     `;
                            //     bowlingTbody.appendChild(tr);
                            // });

                            // Update partnerships
                            const partnershipList = document.querySelector('#partnership-stats');
                            partnershipList.innerHTML = '';

                            if (data.partnerships.length > 0) {
                                data.partnerships.forEach(p => {
                                    let trContent = `<tr>
                                                <th>
                                                    <div class="d-flex align-items-center p-2">
                                                        <img src="${p.batter1.img || ''}" alt="${p.batter1.name}" class="rounded-circle me-3" width="48" height="48" style="object-fit: cover;">
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-0">${p.batter1.name}</h6>
                                                            <small class="text-muted">${p.batter1.role}</small>
                                                        </div>
                                                    </div>
                                                </th>
                                                <td class="text-center">
                                                    <div class="mb-1">
                                                        <small>${p.runs} (${p.balls} balls)</small>
                                                    </div>
                                                    <div class="progress" style="height: 10px;">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: ${p.runsPercent}%" aria-valuenow="${p.runsPercent}" aria-valuemin="0" aria-valuemax="100"></div>
                                                        <div class="progress-bar bg-primary" role="progressbar" style="width: ${100 - p.runsPercent}%" aria-valuenow="${100 - p.runsPercent}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <div class="d-flex align-items-center p-2">
                                                        <div class="flex-grow-1 mr-2">
                                                            <h6 class="mb-0">${p.batter2.name}</h6>
                                                            <small class="text-muted">${p.batter2.role}</small>
                                                        </div>
                                                        <img src="${p.batter2.img || ''}" alt="${p.batter2.name}" class="rounded-circle" width="48" height="48" style="object-fit: cover; margin-left: 15px;">
                                                    </div>
                                                </td>
                                            </tr>`;

                                    // Append each row to the table
                                    partnershipList.innerHTML += trContent;
                                });
                            } else {
                                partnershipList.innerHTML = `<tr>
                                    <th colspan="3" class="text-center">Players Not Entered Yet</th>
                                </tr>`;
                            }

                            // // Update fall of wickets
                            const fallWicketsList = document.querySelector('#fallofwickets-stats');
                            fallWicketsList.innerHTML = '';

                            if (data.fall_of_wickets && data.fall_of_wickets.length > 0) {
                                console.log("Fall Of Wickets: " + data.fall_of_wickets.length)
                                data.fall_of_wickets.forEach(w => {
                                    // w should have: player_name, runs, balls, over
                                    const tr = document.createElement('tr');

                                    tr.innerHTML = `
                                        <th>${w.player_name}</th>
                                        <td class="text-center">${w.runs}-${w.balls}</td>
                                        <td class="text-center">${w.over}</td>
                                    `;

                                    fallWicketsList.appendChild(tr);
                                });
                            } else {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `<th colspan="3" class="text-center">No wickets fallen yet</th>`;
                                fallWicketsList.appendChild(tr);
                            }
                        } else {
                            console.error('Failed to load stats:', data.message);
                        }
                    })
                    .catch(err => console.error('Error fetching stats:', err));
            }

            // ------------------------
            // 🔹 Init
            // ------------------------
            loadYetToBatPlayers();
            loadMatchState();
            loadCurrentStats(matchId);
            fetchBowlingTeamPlayers();
        });
    </script>
@endpush
