@extends('admin.layouts.theme')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.cricket-matches.update', $match->id) }}" method="POST">
                        @csrf
                        @method('POST')

                        <div class="row g-3">
                            {{-- Title --}}
                            <div class="col-md-12">
                                <label for="title" class="form-label">Match Title</label>
                                <input type="text" name="title" class="form-control"
                                    value="{{ old('title', $match->title) }}" required>
                            </div>

                            {{-- Team A --}}
                            <div class="col-md-6">
                                <label for="team_a_id" class="form-label">Team A</label>
                                <select name="team_a_id" class="form-select" required>
                                    @if($teams->count() > 0 || !$match->tournament_id)
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}"
                                            {{ $match->team_a_id == $team->id ? 'selected' : '' }}>
                                            {{ $team->name }}
                                        </option>
                                    @endforeach
                                    @else
                                    <option value="{{ $match->teamA->id }}" selected>
                                        {{ $match->teamA->name }}
                                    </option>
                                    @endif
                                </select>
                            </div>

                            {{-- Team B --}}
                            <div class="col-md-6">
                                <label for="team_b_id" class="form-label">Team B</label>
                                <select name="team_b_id" class="form-select" required>
                                    @if($teams->count() > 0 || !$match->tournament_id)
                                        @foreach ($teams as $team)
                                            <option value="{{ $team->id }}"
                                                {{ $match->team_b_id == $team->id ? 'selected' : '' }}>
                                                {{ $team->name }}
                                            </option>
                                        @endforeach
                                    @else
                                    <option value="{{ $match->teamB->id }}" selected>
                                        {{ $match->teamB->name }}
                                    </option>
                                    @endif
                                </select>
                            </div>

                            {{-- Tournament --}}
                            <div class="col-md-6">
                                <label for="tournament_id" class="form-label">Tournament</label>
                                <select name="tournament_id" class="form-select">
                                    <option value="">-- Select Tournament --</option>
                                    @foreach ($tournaments as $tournament)
                                        <option value="{{ $tournament->id }}"
                                            {{ $match->tournament_id == $tournament->id ? 'selected' : '' }}>
                                            {{ $tournament->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Match Date --}}
                            <div class="col-md-6">
                                <label for="match_date" class="form-label">Match Date</label>
                                <input type="datetime-local" name="match_date" class="form-control"
                                    value="{{ old('match_date', \Carbon\Carbon::parse($match->match_date)->format('Y-m-d\TH:i')) }}"
                                    required>
                            </div>

                            {{-- Venue --}}
                            <div class="col-md-6">
                                <label for="venue" class="form-label">Venue</label>
                                <input type="text" name="venue" class="form-control"
                                    value="{{ old('venue', $match->venue) }}">
                            </div>

                            {{-- Match Type --}}
                            <div class="col-md-6">
                                <label for="match_type" class="form-label">Match Type</label>
                                <select name="match_type" class="form-select" required>
                                    <option value=""></option>
                                    <option value="tournament" {{ $match->match_type == 'tournament' && $match->tournament_id ? 'selected' : '' }}>
                                        Tournament</option>
                                    <option value="regular" {{ $match->match_type == 'regular' && !$match->tournament_id ? 'selected' : '' }}>Regular
                                    </option>
                                </select>
                            </div>

                            {{-- Status --}}
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="upcoming" {{ $match->status == 'upcoming' ? 'selected' : '' }}>Upcoming
                                    </option>
                                    <option value="live" {{ $match->status == 'live' ? 'selected' : '' }}>Live</option>
                                    <option value="completed" {{ $match->status == 'completed' ? 'selected' : '' }}>
                                        Completed</option>
                                </select>
                            </div>

                            {{-- Winning Team --}}
                            <div class="col-md-6">
                                <label for="winning_team_id" class="form-label">Winning Team</label>
                                <select name="winning_team_id" class="form-select">
                                    <option value="">-- Not Decided --</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}"
                                            {{ $match->winning_team_id == $team->id ? 'selected' : '' }}>
                                            {{ $team->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Result Summary --}}
                            <div class="col-md-12">
                                <label for="result_summary" class="form-label">Result Summary</label>
                                <textarea name="result_summary" class="form-control" rows="3">{{ old('result_summary', $match->result_summary) }}</textarea>
                            </div>

                            {{-- Submit --}}
                            <div class="col-md-12 mt-3 text-end">
                                <button type="submit" class="btn btn-primary">Update Match</button>
                            </div>
                        </div>
                    </form>
                </div>
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
