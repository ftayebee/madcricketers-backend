@extends('admin.layouts.theme')

@section('content')
    @push('styles')
        <style>
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
            <div class="card custom-card-border">
                <div class="card-body">
                    <div class="row g-4 align-items-center">
                        {{-- Tournament Logo --}}
                        <div class="col-md-2 text-center">
                            <div class="rounded border p-2 bg-light">
                                @if ($tournament->logo)
                                    <img src="{{ asset('storage/uploads/tournaments/' . $tournament->logo) }}"
                                        class="img-fluid rounded" style="max-height: 100px;" alt="{{ $tournament->name }}">
                                @else
                                    <div class="text-muted small">No Logo</div>
                                @endif
                            </div>
                        </div>

                        {{-- Tournament Basic Info --}}
                        <div class="col-md-8">
                            <h4 class="fw-bold">{{ $tournament->name }}</h4>
                            <p class="mb-1"><strong>Location:</strong> {{ $tournament->location ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Start Date:</strong>
                                {{ \Carbon\Carbon::parse($tournament->start_date)->format('d M, Y') ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>End Date:</strong>
                                {{ \Carbon\Carbon::parse($tournament->end_date)->format('d M, Y') ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Status:</strong>
                                <span
                                    class="badge bg-{{ $tournament->status == 'completed' ? 'success' : ($tournament->status == 'ongoing' ? 'primary' : 'secondary') }}">
                                    {{ ucfirst($tournament->status) }}
                                </span>
                            </p>
                            <p class="mb-1"><strong>Playing Teams:</strong>
                                {{ $tournament->groups->flatMap->teams->unique('id')->count() }}
                            </p>
                            <p class="text-muted">{{ $tournament->description ?? 'No description provided.' }}</p>
                        </div>

                        {{-- Optional Actions --}}
                        <div class="col-md-2 mt-0 text-end">
                            <a href="{{ route('admin.tournaments.edit', $tournament->id) }}"
                                class="btn btn-sm btn-primary">Edit</a>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                data-bs-target="#create-fixture">Create Fixture</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card custom-card-border">
                <div class="card-body">
                    <ul class="nav nav-pills nav-justified p-1">
                        <li class="nav-item">
                            <a href="#profilePillJustified" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">
                                <span class="d-block d-sm-none"><i class="bx bx-user"></i></span>
                                <span class="d-none d-sm-block">Fixtures</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#homePillJustified" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                <span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
                                <span class="d-none d-sm-block">Standings</span>
                            </a>
                        </li>
                    </ul>
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
                                                    <td class="text-center">{{ $stat->net_run_rate ?? 0 }}</td>
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
                                                            <a href="{{ route('admin.cricket-matches.start', ['id' => $match->id]) }}"
                                                                class="btn btn-sm btn-info">Start Match</a>
                                                        @endif

                                                        @if ($match->status == 'live')
                                                            <a href="{{ route('admin.cricket-matches.start', ['id' => $match->id]) }}"
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
                                                    <a href="{{ route('admin.cricket-matches.edit', $match->id) }}"
                                                        class="btn btn-sm btn-outline-primary">Edit</a>
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

                                {{-- Semi Final --}}
                                @if (isset($matchesByStage['semi-final']) && count($matchesByStage['semi-final']) > 0)
                                    <div class="mb-4">
                                        <h5 class="fw-bold mb-3 stage-title">Semi-Finals</h5>
                                        <div class="list-group">
                                            @foreach ($matchesByStage['semi-final'] as $match)
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
                                                    <a href="{{ route('admin.cricket-matches.edit', $match->id) }}"
                                                        class="btn btn-sm btn-outline-primary">Edit</a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="mb-4">
                                        <h5 class="fw-bold mb-3 stage-title">Semi-Finals</h5>
                                        <div class="list-group">
                                            {{-- Static placeholders for planned semi-final matches --}}
                                            <div class="list-group-item">
                                                <strong>TBA</strong><br>
                                                <strong>TBC vs TBC</strong>, 1st Semi-Final<br>
                                                <small class="text-muted">Match starts at TBA</small>
                                            </div>
                                            <div class="list-group-item">
                                                <strong>TBA</strong><br>
                                                <strong>TBC vs TBC</strong>, 2nd Semi-Final<br>
                                                <small class="text-muted">Match starts at TBA</small>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Final --}}
                                <div class="mb-4">
                                    <h5 class="fw-bold mb-3 stage-title">Final Match</h5>
                                    @if (isset($matchesByStage['final']) && $matchesByStage['final']->isNotEmpty())
                                        @php
                                            $final = $matchesByStage['final']->first();
                                            $teamA = $final->teamA->name ?? 'TBA';
                                            $teamB = $final->teamB->name ?? 'TBA';
                                            $matchTime = $final->match_date
                                                ? \Carbon\Carbon::parse($final->match_date)->format('M d, Y - h:i A')
                                                : 'TBA';
                                        @endphp
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $teamA }}</strong> vs
                                                <strong>{{ $teamB }}</strong><br>
                                                <small class="text-muted">{{ $matchTime }} @
                                                    {{ $final->venue ?? 'TBD' }}</small>
                                            </div>
                                            <a href="{{ route('admin.cricket-matches.edit', $final->id) }}"
                                                class="btn btn-sm btn-outline-primary">Edit</a>
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
            })
        });
    </script>
@endpush
