@extends('admin.layouts.theme')

@section('content')
    @push('styles')
        <style>
            .text-right{
                text-align: right!important;
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
    <div class="row">
        <div class="col-sm-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <div class="row g-4 align-items-center">
                                <div class="col-md-2 text-center">
                                    <div class="rounded border p-2 bg-light">
                                        @if ($match->tournament && $match->tournament->logo)
                                            <img src="{{ asset('storage/uploads/tournaments/' . $match->tournament->logo) }}"
                                                class="img-fluid rounded" style="max-height: 100px;"
                                                alt="{{ $match->tournament->name }}">
                                        @else
                                            <div class="text-muted small">No Logo</div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Match Info --}}
                                <div class="col-md-10">
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
                            {{-- Show currently batting team depending on the toss --}}
                            <span id="battingTeamName">Team A</span>
                        </h5>
                        <small>
                            Score: <span id="currentScore">72/2</span> |
                            Overs: <span id="currentOvers">8.3</span> |
                            CRR: <span id="currentCRR">8.47</span> |
                            Projected: <span id="projectedScore">160</span>
                        </small>
                    </div>
                </div>

                <div class="card-body row">
                    {{-- Left Side: Batting players and scoring buttons --}}
                    <div class="col-md-8">
                        <div class="row">
                            {{-- Player 1 --}}
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body d-flex justify-content-between">
                                        <div class="d-flex align-items-center justify-content-start">
                                            <img src="" alt="" >
                                            <div>
                                                <h5 id="strikerName">Choose Player</h5>
                                                <p>Runs: <span id="strikerRuns">00</span> (0 balls)</p>
                                            </div>
                                        </div>
                                        <button class="btn btn-danger btn-sm" onclick="markAsOut('striker')">Out</button>
                                    </div>
                                </div>
                            </div>

                            {{-- Player 2 --}}
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body d-flex justify-content-between">
                                        <div>
                                            <h5 id="nonStrikerName">Choose Player</h5>
                                            <p>Runs: <span id="nonStrikerRuns">00</span> (0 balls)</p>
                                        </div>
                                        <button class="btn btn-danger btn-sm" onclick="markAsOut('striker')">Out</button>
                                    </div>
                                </div>
                            </div>

                            {{-- Scoring buttons --}}
                            <div class="col-12">
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    @foreach (['0', '1', '2', '3', '4', '6', 'NB', 'LB', 'WD', 'W'] as $run)
                                        <button class="btn btn-outline-primary"
                                            onclick="recordRun('{{ $run }}')">{{ $run }}</button>
                                    @endforeach
                                </div>

                                {{-- Over display --}}
                                <div class="mt-3">
                                    <strong>Current Over:</strong>
                                    <span id="currentOverDetails">1 0 4 . 6</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <h4 style="font-weight: bold; border-radius: 5px;" class="bg-soft-info py-2 text-uppercase">Batting</h4>

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
                                    @php
                                        $players = \App\Models\Team::with('players')->where('id', $match->team_a_id)->first()->players;
                                    @endphp
                                    @foreach ($players as $player)
                                        <tr>
                                            <th>{{$player->user->full_name}}</th>
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-4">
                            <h4 style="font-weight: bold; border-radius: 5px;" class="bg-soft-info py-2 text-uppercase">Bowling</h4>

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
                                <tbody id="batting-stats">
                                    @php
                                        $players = \App\Models\Team::with('players')->where('id', $match->team_b_id)->first()->players;
                                    @endphp
                                    @foreach ($players as $player)
                                        <tr>
                                            <th>{{$player->user->full_name}}</th>
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-4">
                            <h4 style="font-weight: bold; border-radius: 5px;" class="bg-soft-info py-2">FALL OF WICKETS</h4>

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th width="50%" class="text-left">Batsman</th>
                                        <th width="10%" class="text-center">Score</th>
                                        <th width="10%" class="text-center">Overs</th>
                                    </tr>
                                </thead>
                                <tbody id="batting-stats">
                                    @php
                                        $players = \App\Models\Team::with('players')->where('id', $match->team_a_id)->first()->players;
                                    @endphp
                                    @foreach ($players as $player)
                                        <tr>
                                            <th>{{$player->user->full_name}}</th>
                                            <td class="text-center">0-0</td>
                                            <td class="text-center">0.0</td>
                                        </tr>
                                    @endforeach
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
                                <tbody id="batting-stats">
                                    @php
                                        $players = \App\Models\Team::with('players')->where('id', $match->team_a_id)->first()->players;
                                    @endphp
                                    @foreach ($players as $player)
                                        <tr>
                                            <th>
                                                <div class="d-flex align-items-center p-2">
                                                    <img src="{{$player->image}}" alt="{{$player->user->full_name}}" class="rounded-circle me-3" width="48" height="48" style="object-fit: cover;">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-0">{{$player->user->full_name}}</h6>
                                                        <small class="text-muted">{{$player->player_role}}</small>
                                                    </div>
                                                </div>
                                            </th>
                                            <td class="text-center">
                                                <!-- Label: runs (balls) -->
                                                @php
                                                    $player1Percent = 61;
                                                    $player2Percent = 39;
                                                @endphp
                                                <div class="mb-1">
                                                    <small>0 (0 balls)</small>
                                                </div>
                                                
                                                <!-- Dual color progress bar -->
                                                <div class="progress" style="height: 10px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: {{$player1Percent}}%" 
                                                        aria-valuenow="{{$player1Percent}}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    <div class="progress-bar bg-primary" role="progressbar"
                                                        style="width: {{$player2Percent}}%" 
                                                        aria-valuenow="{{$player2Percent}}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </td>
                                            <td class="text-right">
                                                <div class="d-flex align-items-center p-2">
                                                    <div class="flex-grow-1 mr-2">
                                                        <h6 class="mb-0">{{$player->user->full_name}}</h6>
                                                        <small class="text-muted">{{$player->player_role}}</small>
                                                    </div>
                                                    <img src="{{$player->image}}" alt="{{$player->user->full_name}}" class="rounded-circle" width="48" height="48" style="object-fit: cover; margin-left: 15px;">
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Right Sidebar: Yet to Bat --}}
                    <div class="col-md-4">
                        <h4 style="font-weight: bold;">Yet to Bat</h4>
                        <div id="yetToBatList" style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 1rem;">
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
                            console.log("Toss data stored:", response);
                            // Optional: Show success message or disable inputs
                        },
                        error: function(xhr) {
                            console.error("Toss store failed", xhr.responseText);
                            // Optional: Show error alert
                        }
                    });
                }
            }

            function markAsOut(player) {
                alert(player + ' marked as out'); // Replace with actual logic
            }

            function recordRun(run) {
                alert('Run scored: ' + run); // Replace with actual logic
            }

            // Example: Real-time loading of Yet to Bat players
            function loadYetToBatPlayers() {
                fetch('/api/matches/yet-to-bat/'+matchId)
                    .then(res => res.json())
                    .then(data => {
                        if(!data.success) return;

                        if (data.players.length) {
                            const list = document.getElementById('yetToBatList');
                            list.innerHTML = '';

                            data.players.forEach(player => {
                                const card = document.createElement('div');
                                card.className = 'card mb-2';

                                card.innerHTML = `
                                    <div class="d-flex align-items-center p-2 border">
                                        <img src="${player.image}" alt="${player.short_name}" class="rounded-circle me-3" width="48" height="48" style="object-fit: cover;">
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

            // setInterval(loadYetToBatPlayers, 5000); // Update every 5 sec (optional)
            loadYetToBatPlayers(); // Initial load
        });
    </script>
@endpush
