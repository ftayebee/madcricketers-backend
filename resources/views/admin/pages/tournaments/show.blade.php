@extends('admin.layouts.theme')

@section('content')
    @push('styles')
        <style>
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

            .stage-title {
                padding: 12px 20px;
                text-align: center;
                font-weight: bold;
                border-radius: 5px;
                background: rgb(120 243 255 / 15%);
                /* translucent background */
                backdrop-filter: blur(10px);
                /* glass effect */
                -webkit-backdrop-filter: blur(10px);
                border: 2px solid transparent;
                background-clip: padding-box;
                position: relative;
                z-index: 1;
                color: #111;
            }

            .stage-title::before {
                content: "";
                position: absolute;
                inset: 0;
                border-radius: 5px;
                padding: 2px;
                background: linear-gradient(135deg, #89f7fe, #66a6ff);
                /* attractive gradient border */
                -webkit-mask:
                    linear-gradient(#fff 0 0) content-box,
                    linear-gradient(#fff 0 0);
                -webkit-mask-composite: xor;
                mask-composite: exclude;
                z-index: -1;
            }
        </style>
    @endpush
    <div class="row">
        <div class="col-sm-12">
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        {{-- Tournament Logo --}}
                        <div class="col-md-2 text-center">
                            <div class="rounded-circle overflow-hidden border border-2 p-1 bg-light d-flex align-items-center justify-content-center"
                                style="width:100px; height:100px; margin:auto;">
                                @if ($tournament->logo)
                                    <img src="{{ asset('storage/uploads/tournaments/' . $tournament->logo) }}"
                                        class="img-fluid" style="width:100%; height:100%; object-fit:cover;"
                                        alt="{{ $tournament->name }}">
                                @else
                                    <span class="text-muted small">No Logo</span>
                                @endif
                            </div>
                        </div>

                        {{-- Tournament Basic Info --}}
                        <div class="col-md-8">
                            <h3 class="fw-bold mb-2">{{ $tournament->name }}</h3>
                            <div class="row text-muted mb-2">
                                <div class="col-12 col-sm-6 mb-1"><strong>Location:</strong>
                                    {{ $tournament->location ?? 'N/A' }}</div>
                                <div class="col-12 col-sm-6 mb-1"><strong>Playing Teams:</strong>
                                    {{ $tournament->groups->flatMap->teams->unique('id')->count() }}</div>
                                <div class="col-12 col-sm-6 mb-1"><strong>Start Date:</strong>
                                    {{ \Carbon\Carbon::parse($tournament->start_date)->format('d M, Y') ?? 'N/A' }}</div>
                                <div class="col-12 col-sm-6 mb-1"><strong>End Date:</strong>
                                    {{ \Carbon\Carbon::parse($tournament->end_date)->format('d M, Y') ?? 'N/A' }}</div>
                                <div class="col-12 col-sm-6 mb-1"><strong>Status:</strong>
                                    <span
                                        class="badge 
                            {{ $tournament->status == 'completed' ? 'bg-success' : ($tournament->status == 'ongoing' ? 'bg-primary' : 'bg-secondary') }}">
                                        {{ ucfirst($tournament->status) }}
                                    </span>
                                </div>
                            </div>
                            <p class="text-muted small mb-0">{{ $tournament->description ?? 'No description provided.' }}
                            </p>
                        </div>

                        {{-- Actions --}}
                        <div class="col-md-2 text-md-end d-flex flex-column gap-2 mt-2 mt-md-0">
                            <a href="{{ route('admin.tournaments.edit', $tournament->id) }}"
                                class="btn btn-sm btn-outline-primary w-100">
                                <i class="ri-edit-line me-1"></i> Edit
                            </a>
                            <button class="btn btn-sm btn-primary w-100" data-bs-toggle="modal"
                                data-bs-target="#create-fixture">
                                <i class="ri-calendar-line me-1"></i> Create Fixture
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4 mt-4">
                <div class="card-header bg-light rounded-top py-2 px-3">
                    <h5 class="mb-0 fw-bold">Tournament Key Stats</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @php
                            $allPlayers = $tournament->groups->flatMap->teams->flatMap->players->unique('id');

                            $stats = [
                                'Most Runs' => $allPlayers
                                    ->map(
                                        fn($player) => [
                                            'player' => $player,
                                            'runs' => $player
                                                ->matches()
                                                ->where('tournament_id', $tournament->id)
                                                ->sum('match_players.runs_scored'),
                                        ],
                                    )
                                    ->sortByDesc('runs')
                                    ->first(),

                                'Most Wickets' => $allPlayers
                                    ->map(
                                        fn($player) => [
                                            'player' => $player,
                                            'wickets' => $player
                                                ->matches()
                                                ->where('tournament_id', $tournament->id)
                                                ->sum('match_players.wickets_taken'),
                                        ],
                                    )
                                    ->sortByDesc('wickets')
                                    ->first(),

                                'Most Sixes' => $allPlayers
                                    ->map(
                                        fn($player) => [
                                            'player' => $player,
                                            'sixes' => $player
                                                ->matches()
                                                ->where('tournament_id', $tournament->id)
                                                ->sum('match_players.sixes'),
                                        ],
                                    )
                                    ->sortByDesc('sixes')
                                    ->first(),

                                'Best Strike Rate' => $allPlayers
                                    ->map(
                                        fn($player) => [
                                            'player' => $player,
                                            'strike_rate' =>
                                                $player
                                                    ->matches()
                                                    ->where('tournament_id', $tournament->id)
                                                    ->sum('match_players.balls_faced') > 0
                                                    ? round(
                                                        ($player
                                                            ->matches()
                                                            ->where('tournament_id', $tournament->id)
                                                            ->sum('match_players.runs_scored') /
                                                            $player
                                                                ->matches()
                                                                ->where('tournament_id', $tournament->id)
                                                                ->sum('match_players.balls_faced')) *
                                                            100,
                                                        2,
                                                    )
                                                    : 0,
                                        ],
                                    )
                                    ->sortByDesc('strike_rate')
                                    ->first(),

                                'Best Economy Rate' => $allPlayers
                                    ->map(
                                        fn($player) => [
                                            'player' => $player,
                                            'economy_rate' =>
                                                $player
                                                    ->matches()
                                                    ->where('tournament_id', $tournament->id)
                                                    ->sum('match_players.overs_bowled') > 0
                                                    ? round(
                                                        $player
                                                            ->matches()
                                                            ->where('tournament_id', $tournament->id)
                                                            ->sum('match_players.runs_conceded') /
                                                            $player
                                                                ->matches()
                                                                ->where('tournament_id', $tournament->id)
                                                                ->sum('match_players.overs_bowled'),
                                                        2,
                                                    )
                                                    : 0,
                                        ],
                                    )
                                    ->sortBy('economy_rate')
                                    ->first(), // lowest is best

                                'Best Bowling Figure' => $allPlayers
                                    ->map(
                                        fn($player) => [
                                            'player' => $player,
                                            'figure' => $player
                                                ->matches()
                                                ->where('tournament_id', $tournament->id)
                                                ->max('match_players.wickets_taken'),
                                        ],
                                    )
                                    ->sortByDesc('figure')
                                    ->first(),

                                'Highest Score' => $allPlayers
                                    ->map(
                                        fn($player) => [
                                            'player' => $player,
                                            'runs' => $player
                                                ->matches()
                                                ->where('tournament_id', $tournament->id)
                                                ->max('match_players.runs_scored'),
                                        ],
                                    )
                                    ->sortByDesc('runs')
                                    ->first(),
                            ];
                        @endphp

                        @foreach ($stats as $label => $stat)
                            @if ($stat && $stat['player'])
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                    <div class="d-flex align-items-center p-3 border rounded shadow-sm bg-white h-100">
                                        <div class="icon-wrapper bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center me-3"
                                            style="width:50px; height:50px; font-size:1.5rem;">
                                            {{-- Use different icons per stat --}}
                                            @switch($label)
                                                @case('Most Runs')
                                                    <i class="ri-run-line"></i>
                                                @break

                                                @case('Most Wickets')
                                                    <i class="ri-cricket-line"></i>
                                                @break

                                                @case('Most Sixes')
                                                    <i class="ri-6-line"></i>
                                                @break

                                                @case('Best Strike Rate')
                                                    <i class="ri-flashlight-line"></i>
                                                @break

                                                @case('Best Economy Rate')
                                                    <i class="ri-speed-line"></i>
                                                @break

                                                @case('Best Bowling Figure')
                                                    <i class="ri-award-line"></i>
                                                @break

                                                @case('Highest Score')
                                                    <i class="ri-4-line"></i>
                                                @break

                                                @default
                                                    <i class="ri-trophy-line"></i>
                                            @endswitch
                                        </div>
                                        <div class="stat-details flex-grow-1">
                                            <h6 class="mb-1 text-muted">{{ $label }}</h6>
                                            <h4 class="d-flex align-items-center justify-content-between m-0">
                                                <span class="fw-bold">{{ $stat['player']->user->full_name }}</span>
                                                <span class="badge bg-light text-dark fs-20">
                                                    {{ $stat['runs'] ?? ($stat['wickets'] ?? ($stat['sixes'] ?? ($stat['strike_rate'] ?? ($stat['economy_rate'] ?? ($stat['figure'] ?? 0))))) }}
                                                </span>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card custom-card-border">
                <div class="card-header p-0">
                    <ul class="nav nav-pills nav-justified" id="inningsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a href="#profilePillJustified" data-bs-toggle="tab" class="nav-link scoreboard-btn active"
                                id="inning-tab-0" aria-selected="true" role="tab">
                                Fixtures
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="#homePillJustified" data-bs-toggle="tab" class="nav-link scoreboard-btn"
                                id="inning-tab-1" aria-selected="false" role="tab" tabindex="-1">
                                Standings
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content pt-2 text-muted">
                        <div class="tab-pane" id="homePillJustified">
                            @forelse ($tournament->groups as $group)
                                <h6 class="mb-3">{{ $group->name }}</h6>

                                @php
                                    $groupTeams = $group->teams;
                                    $stats = \App\Models\TournamentTeamStat::where('tournament_id', $tournament->id)
                                        ->whereIn('team_id', $groupTeams->pluck('id'))
                                        ->get()
                                        ->keyBy('team_id');

                                    $groupTeams = $groupTeams
                                        ->sortByDesc(function ($team) use ($stats) {
                                            $stat = $stats[$team->id] ?? null;
                                            return [$stat->points ?? 0, $stat->net_run_rate ?? 0];
                                        })
                                        ->values();

                                    $allGroupMatchesCompleted =
                                        $tournament->matches
                                            ->where('status', '!=', 'completed')
                                            ->whereIn('group_id', [$group->id])
                                            ->count() === 0;
                                @endphp

                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered align-middle table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Team</th>
                                                <th class="text-center">M</th>
                                                <th class="text-center">W</th>
                                                <th class="text-center">L</th>
                                                <th class="text-center">NR</th>
                                                <th class="text-center">Pts</th>
                                                <th class="text-center">NRR</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($groupTeams as $index => $team)
                                                @php
                                                    $stat = $stats[$team->id] ?? null;
                                                    $qualified = $allGroupMatchesCompleted && $index < 2;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        {{ $team->name }}
                                                        @if ($qualified)
                                                            <span class="badge bg-success ms-2">Q</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ $stat->matches_played ?? 0 }}</td>
                                                    <td class="text-center">{{ $stat->wins ?? 0 }}</td>
                                                    <td class="text-center">{{ $stat->losses ?? 0 }}</td>
                                                    <td class="text-center">{{ $stat->no_results ?? 0 }}</td>
                                                    <td class="text-center">{{ $stat->points ?? 0 }}</td>
                                                    <td class="text-center">{{ $stat->nrr ?? 0 }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @empty
                                <p>No group standings available yet.</p>
                            @endforelse
                        </div>
                        <div class="tab-pane show active" id="profilePillJustified">
                            @if ($tournament->matches->count() > 0)
                                @php
                                    $matchesByStage = $tournament->matches->groupBy('stage');
                                @endphp

                                {{-- Group Stage --}}
                                @if (isset($matchesByStage['group']))
                                    <div class="mb-4">
                                        <h5 class="fw-bold mb-3 stage-title">Group Stage Matches</h5>
                                        <div class="list-group">
                                            @foreach (collect($matchesByStage['group'])->sortBy('match_date') as $match)
                                                @php
                                                    $teamA = $match->teamA->name ?? 'TBA';
                                                    $teamB = $match->teamB->name ?? 'TBA';
                                                    $matchTime = $match->match_date
                                                        ? \Carbon\Carbon::parse($match->match_date)->format(
                                                            'M d, Y - h:i A',
                                                        )
                                                        : 'TBA';
                                                    $groupA = optional(
                                                        $match->teamA
                                                            ->groups()
                                                            ->where(
                                                                'tournament_group_teams.tournament_id',
                                                                $tournament->id,
                                                            )
                                                            ->first(),
                                                    )->name;
                                                    $groupB = optional(
                                                        $match->teamB
                                                            ->groups()
                                                            ->where(
                                                                'tournament_group_teams.tournament_id',
                                                                $tournament->id,
                                                            )
                                                            ->first(),
                                                    )->name;
                                                @endphp
                                                <div
                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>{{ $teamA }}</strong>
                                                        @if ($groupA)
                                                            <small class="text-muted">({{ $groupA }})</small>
                                                        @endif
                                                        vs
                                                        <strong>{{ $teamB }}</strong>
                                                        @if ($groupB)
                                                            <small class="text-muted">({{ $groupB }})</small>
                                                        @endif
                                                        <br>
                                                        <small class="text-muted">{{ $matchTime }} @
                                                            {{ $match->venue ?? 'TBD' }}</small>
                                                    </div>
                                                    <div>
                                                        @if (Auth::user()->can('cricket-matches-start') && $match->status == 'upcoming')
                                                            <button
                                                                data-action="{{ route('admin.cricket-matches.start', ['id' => $match->id]) }}"
                                                                data-forwardUrl="{{ route('admin.cricket-matches.scoreboard.view', ['id' => $match->id]) }}"
                                                                data-match="{{ $match->id }}"
                                                                class="btn btn-sm btn-info btn-start-match">Start
                                                                Match</button>
                                                        @endif

                                                        @if ($match->status == 'live')
                                                            <a href="{{ route('admin.cricket-matches.scoreboard.view', ['id' => $match->id]) }}"
                                                                class="btn btn-sm btn-info">Scoreboard</a>
                                                        @endif

                                                        @if (Auth::user()->can('cricket-matches-view'))
                                                            <a href="{{ route('admin.cricket-matches.show', ['id' => $match->id]) }}"
                                                                class="btn btn-sm btn-warning">View Stats</a>
                                                        @endif

                                                        @if (Auth::user()->can('cricket-matches-edit'))
                                                            <a href="{{ route('admin.cricket-matches.edit', ['id' => $match->id]) }}"
                                                                class="btn btn-sm btn-primary">Edit</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Playoffs --}}
                                @if (isset($matchesByStage['playoffs']) && count($matchesByStage['playoffs']) > 0)
                                    <div class="mb-4">
                                        <h5 class="fw-bold mb-3 stage-title">Playoff Matches (Quarter Finals)</h5>
                                        <div class="list-group">
                                            @foreach ($matchesByStage['playoffs'] as $match)
                                                @php
                                                    $teamA = $match->teamA->name ?? 'TBA';
                                                    $teamB = $match->teamB->name ?? 'TBA';
                                                    $matchTime = $match->match_date
                                                        ? \Carbon\Carbon::parse($match->match_date)->format(
                                                            'M d, Y - h:i A',
                                                        )
                                                        : 'TBA';
                                                @endphp
                                                <div
                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>{{ $teamA }}</strong> vs
                                                        <strong>{{ $teamB }}</strong><br>
                                                        <small class="text-muted">
                                                            {{ $matchTime }} @ {{ $match->venue ?? 'TBD' }}
                                                        </small>
                                                    </div>
                                                    <div>
                                                        @if (Auth::user()->can('cricket-matches-start') && $match->status == 'upcoming')
                                                            <button
                                                                data-action="{{ route('admin.cricket-matches.start', ['id' => $match->id]) }}"
                                                                data-forwardUrl="{{ route('admin.cricket-matches.scoreboard.view', ['id' => $match->id]) }}"
                                                                data-match="{{ $match->id }}"
                                                                class="btn btn-sm btn-info btn-start-match">Start
                                                                Match</button>
                                                        @endif

                                                        @if ($match->status == 'live')
                                                            <a href="{{ route('admin.cricket-matches.scoreboard.view', ['id' => $match->id]) }}"
                                                                class="btn btn-sm btn-info">Scoreboard</a>
                                                        @endif

                                                        @if (Auth::user()->can('cricket-matches-view'))
                                                            <a href="{{ route('admin.cricket-matches.show', ['id' => $match->id]) }}"
                                                                class="btn btn-sm btn-warning">View Stats</a>
                                                        @endif

                                                        @if (Auth::user()->can('cricket-matches-edit'))
                                                            <a href="{{ route('admin.cricket-matches.edit', ['id' => $match->id]) }}"
                                                                class="btn btn-sm btn-primary">Edit</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="mb-4">
                                        <h5 class="fw-bold mb-3 stage-title">Playoff Matches (Quarter Finals)</h5>
                                        <div class="list-group">
                                            @for ($i = 1; $i <= 4; $i++)
                                                <div class="list-group-item">
                                                    <strong>TBA</strong><br>
                                                    <strong>TBC vs TBC</strong>, Quarter Final {{ $i }}<br>
                                                    <small class="text-muted">
                                                        Match starts at TBA
                                                    </small>
                                                </div>
                                            @endfor
                                        </div>
                                    </div>
                                @endif

                                {{-- Final --}}
                                <div class="mb-4">
                                    <h5 class="fw-bold mb-3 stage-title">Final Match</h5>
                                    @if (isset($matchesByStage['final']) && $matchesByStage['final']->isNotEmpty())
                                        <div class="list-group">
                                            @php
                                                $final = $matchesByStage['final']->first();
                                                $teamA = $final->teamA->name ?? 'TBA';
                                                $teamB = $final->teamB->name ?? 'TBA';
                                                $matchTime = $final->match_date
                                                    ? \Carbon\Carbon::parse($final->match_date)->format(
                                                        'M d, Y - h:i A',
                                                    )
                                                    : 'TBA';
                                            @endphp
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ $teamA }}</strong> vs
                                                    <strong>{{ $teamB }}</strong><br>
                                                    <small class="text-muted">{{ $matchTime }} @
                                                        {{ $final->venue ?? 'TBD' }}</small>
                                                </div>
                                                <div>
                                                    @if (Auth::user()->can('cricket-matches-start') && $match->status == 'upcoming')
                                                        <button
                                                            data-action="{{ route('admin.cricket-matches.start', ['id' => $match->id]) }}"
                                                            data-forwardUrl="{{ route('admin.cricket-matches.scoreboard.view', ['id' => $match->id]) }}"
                                                            data-match="{{ $match->id }}"
                                                            class="btn btn-sm btn-info btn-start-match">Start
                                                            Match</button>
                                                    @endif

                                                    @if ($match->status == 'live')
                                                        <a href="{{ route('admin.cricket-matches.scoreboard.view', ['id' => $match->id]) }}"
                                                            class="btn btn-sm btn-info">Scoreboard</a>
                                                    @endif

                                                    @if (Auth::user()->can('cricket-matches-view'))
                                                        <a href="{{ route('admin.cricket-matches.show', ['id' => $match->id]) }}"
                                                            class="btn btn-sm btn-warning">View Stats</a>
                                                    @endif

                                                    @if (Auth::user()->can('cricket-matches-edit'))
                                                        <a href="{{ route('admin.cricket-matches.edit', ['id' => $match->id]) }}"
                                                            class="btn btn-sm btn-primary">Edit</a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="list-group">
                                            <div class="list-group-item">
                                                <strong>TBA</strong><br>
                                                <strong>TBC vs TBC</strong>, Grand Final<br>
                                                <small class="text-muted">Match starts at TBA</small>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="alert alert-danger text-center" role="alert">
                                    <h5 class="m-0">No Matches Available Yet! Please create Fixtures first!</h5>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="create-fixture" tabindex="-1" aria-labelledby="add-tournamentTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-tournamentTitle">Create Fixture</h5>
                    <p class="modal-title" id="add-tournamentSubTitle"></p>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('admin.tournaments.generate-fixtures') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="status" class="form-label">Match Stage</label>
                                <select name="match_stage" class="form-select form-control">
                                    <option value="group">Group</option>
                                    <option value="playoffs">Playoffs</option>
                                    <option value="final">Final</option>
                                </select>
                                <input type="hidden" name="tournament_id" value="{{ $tournament->id }}">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Generate Fixture</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('select[name="match_stage"]').select2({
                autoWidth: false,
                width: "100%",
                dropdownParent: $('#create-fixture'),
                minimumResultsForSearch: -1
            });

            $(".btn-start-match").on('click', function() {
                const routeUrl = $(this).attr('data-action');
                const forwardUrl = $(this).attr('data-forwardUrl');
                const matchId = $(this).attr('data-match');

                $.ajax({
                    url: routeUrl,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Information!',
                                'Cricket Match Has Been Updated to Status LIVE',
                                'success'
                            ).then(function() {
                                window.location.href = forwardUrl;
                            });
                        }
                    },
                    error: function(response) {
                        Swal.fire(
                            'Error!',
                            'There was a problem deleting the tournament.',
                            'error'
                        );
                    }
                });
            });
        });
    </script>
@endpush
