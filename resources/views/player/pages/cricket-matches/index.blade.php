@extends('player.layouts.theme')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap py-3"
                    style="border-bottom: 2px solid #eef0f4;">
                    <h5 class="mb-0 fw-bold" style="font-size: 18px;">
                        <i class="ti ti-ball-football text-primary me-1"></i> Cricket Matches List
                    </h5>

                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill" style="font-size: 12px;">
                        Updated Overview
                    </span>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12 table-responsive">
                            <table id="tbl-cricket-matches"
                                class="table table-bordered table-striped table-hover text-center align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="background: rgb(17, 15, 34); border-" rowspan="2">Match Detail</th>
                                        <th style="background: rgb(17, 15, 34); border-" colspan="3">Match Information</th>
                                        <th style="background: rgb(17, 15, 34); border-" colspan="2">Batting</th>
                                        <th style="background: rgb(17, 15, 34); border-" colspan="2">Bowling</th>
                                    </tr>

                                    <tr class="table-info">
                                        <th class="text-light" style="background: rgb(32, 29, 58);">Team A Score</th>
                                        <th class="text-light" style="background: rgb(32, 29, 58);">Team B Score</th>
                                        <th class="text-light" style="background: rgb(32, 29, 58);">Result</th>
                                        <th class="text-light" style="background: rgb(32, 29, 58);">Runs</th>
                                        <th class="text-light" style="background: rgb(32, 29, 58);">Strike Rate</th>
                                        <th class="text-light" style="background: rgb(32, 29, 58);">Overs</th>
                                        <th class="text-light" style="background: rgb(32, 29, 58);">Economy</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentMatches as $matchInfo)
                                        @php
                                            $scoreBoardTeamA = $matchInfo->scoreboard
                                                ->where('team_id', $matchInfo->teamA->id)
                                                ->first();
                                            $scoreBoardTeamB = $matchInfo->scoreboard
                                                ->where('team_id', $matchInfo->teamB->id)
                                                ->first();
                                            $playerStat = \App\Models\MatchPlayer::where('match_id', $matchInfo->id)
                                                ->where('player_id', Auth::user()->player->id)
                                                ->first();

                                            $strikeRate = 0;
                                            $economyRate = 0;

                                            if ($playerStat) {
                                                if ($playerStat->balls_faced > 0) {
                                                    $strikeRate = round(
                                                        ($playerStat->runs_scored / $playerStat->balls_faced) * 100,
                                                        2,
                                                    );
                                                }
                                                if ($playerStat->overs_bowled > 0) {
                                                    $economyRate = round(
                                                        $playerStat->runs_conceded / $playerStat->overs_bowled,
                                                        2,
                                                    );
                                                }
                                            }
                                        @endphp
                                        <tr class="align-middle">
                                            @php
                                                $isTeamA = $matchInfo->teamA->players->contains(
                                                    Auth::user()->player->id,
                                                );
                                                $isTeamB = $matchInfo->teamB->players->contains(
                                                    Auth::user()->player->id,
                                                );
                                            @endphp
                                            <!-- Match Details -->
                                            <td class="text-start">
                                                <strong>{{ $matchInfo->title }}</strong> <br>

                                                {{-- Teams --}}
                                                <span class="fs-16 d-block">
                                                    {{ $matchInfo->teamA->name }}
                                                    @if ($isTeamA)
                                                        <span class="badge bg-success ms-1" style="font-size: 10px;">OWN
                                                            TEAM</span>
                                                    @endif

                                                    <span class="mx-1">vs</span>

                                                    {{ $matchInfo->teamB->name }}
                                                    @if ($isTeamB)
                                                        <span class="badge bg-success ms-1" style="font-size: 10px;">OWN
                                                            TEAM</span>
                                                    @endif
                                                </span>

                                                {{-- Match Date --}}
                                                <small>{{ \Carbon\Carbon::parse($matchInfo->match_date)->format('d M, Y') }}</small>

                                                {{-- Tournament --}}
                                                @if ($matchInfo->tournament)
                                                    <br><small
                                                        class="text-muted">{{ $matchInfo->tournament->title }}</small>
                                                @endif
                                            </td>

                                            <!-- Team Scores -->
                                            <td class="fw-bold {{ $isTeamA ? 'text-success' : 'text-dark' }} fs-16">
                                                {{ $scoreBoardTeamA->runs ?? 0 }} - {{ $scoreBoardTeamA->wickets ?? 0 }}
                                                <br>
                                                <small class="fs-14">Overs: {{ $scoreBoardTeamA->overs ?? 0 }} /
                                                    {{ $matchInfo->max_overs }}</small>
                                            </td>
                                            <td class="fw-bold {{ $isTeamB ? 'text-success' : 'text-dark' }}">
                                                <span class="fs-16 d-block">{{ $scoreBoardTeamB->runs ?? 0 }} -
                                                    {{ $scoreBoardTeamB->wickets ?? 0 }}</span>
                                                <small class="fs-14">Overs: {{ $scoreBoardTeamB->overs ?? 0 }} /
                                                    {{ $matchInfo->max_overs }}</small>
                                            </td>
                                            <td class="fw-bold text-warning">{{ $matchInfo->result ?? '-' }}</td>

                                            <!-- Batting -->
                                            <td>
                                                <span class="fs-16 d-block">{{ $playerStat->runs_scored ?? 0 }}
                                                    ({{ $playerStat->balls_faced ?? 0 }}
                                                    balls)</span>
                                                <small>Fours: {{ $playerStat->fours ?? 0 }} | Sixes:
                                                    {{ $playerStat->sixes ?? 0 }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark fs-16">{{ $strikeRate }}</span>
                                            </td>

                                            <!-- Bowling -->
                                            <td>
                                                {{ $playerStat->runs_conceded ?? 0 }} -
                                                {{ $playerStat->wickets_taken ?? 0 }} <br>
                                                <small>{{ $playerStat->overs_bowled ?? 0 }} Overs</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark fs-16">{{ $economyRate }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="mt-3 d-flex justify-content-center">
                                {{ $recentMatches->links() }}
                            </div>
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
            const redirectTo = "{{ url()->current() }}"
            // $('#tbl-cricket-matches').DataTable({
            //     processing: false,
            //     serverSide: false,
            //     searching: true,
            //     responsive: true,
            //     autoWidth: false,
            //     destroy: true,
            // });

            $('.select2').select2({
                width: "100%",
                dropdownParent: $('#add-cricket-match'),

            });

            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                new bootstrap.Tooltip(el);
            });

            const formatSelector = document.getElementById("format");

            const toggleFields = () => {
                const value = formatSelector.value;
                console.log(value)
                document.querySelectorAll(".format-dependent").forEach(el => el.style.display = "none");

                if (value === "group") {
                    document.getElementById("group-fields").style.display = "block";
                }
            };

            formatSelector.addEventListener("change", toggleFields);
            toggleFields();

            $(document).on('click', '.btn-delete', function() {
                const selectedId = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('admin.cricket-matches.destroy', ':selectedId') }}'
                                .replace(':selectedId', selectedId),
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire(
                                    'Deleted!',
                                    'The cricket matches has been deleted.',
                                    'success'
                                );
                                $('#tbl-cricket-matches').DataTable().ajax.reload();
                            },
                            error: function(response) {
                                console.log(response)
                                Swal.fire(
                                    'Error!',
                                    'There was a problem deleting the match.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
