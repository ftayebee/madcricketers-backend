@extends('admin.layouts.theme')

@section('content')
    @php
        $selectedMatchType = old('match_type', request('type', 'regular'));
    @endphp
    @push('styles')
        <style>
            .cs-label-color { color: #002450; }
            .required-mark  { color: red; font-size: 16px; font-weight: 800; }
        </style>
    @endpush

    <div class="row">
        <div class="col-sm-12">
            <div class="card custom-card-border">
                <div class="card-header bg-soft-cyan">
                    <h5 class="text-center fs-24 fw-bold m-0">Create New Match</h5>
                </div>
                <div class="card-body">

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.cricket-matches.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">

                            {{-- Match Title (optional - auto-generated if blank) --}}
                            <div class="col-md-12">
                                <label class="form-label fs-16 cs-label-color">Match Title</label>
                                <input type="text" name="title" class="form-control"
                                       value="{{ old('title') }}"
                                       placeholder="Leave blank to auto-generate (Team A vs Team B)">
                            </div>

                            {{-- Match Type --}}
                            <div class="col-md-6">
                                <label class="form-label fs-16 cs-label-color">
                                    Match Type <span class="required-mark">*</span>
                                </label>
                                <select name="match_type" id="match_type" class="form-select" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="regular"    {{ $selectedMatchType === 'regular'    ? 'selected' : '' }}>Regular</option>
                                    <option value="tournament" {{ $selectedMatchType === 'tournament' ? 'selected' : '' }}>Tournament</option>
                                </select>
                            </div>

                            {{-- Tournament (only for tournament type) --}}
                            <div class="col-md-6" id="tournament-field" style="display:none;">
                                <label class="form-label fs-16 cs-label-color">Tournament</label>
                                <select name="tournament_id" id="tournament_id" class="form-select">
                                    <option value="">-- Select Tournament --</option>
                                    @foreach($tournaments as $tournament)
                                        <option value="{{ $tournament->id }}"
                                            {{ old('tournament_id') == $tournament->id ? 'selected' : '' }}>
                                            {{ $tournament->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Team A --}}
                            <div class="col-md-6">
                                <label class="form-label fs-16 cs-label-color">
                                    Team A <span class="required-mark">*</span>
                                </label>
                                <select name="team_a_id" id="team_a_id" class="form-select" required>
                                    <option value="">-- Select Team A --</option>
                                    @foreach($teams as $team)
                                        <option value="{{ $team->id }}"
                                            {{ old('team_a_id') == $team->id ? 'selected' : '' }}>
                                            {{ $team->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Team B --}}
                            <div class="col-md-6">
                                <label class="form-label fs-16 cs-label-color">
                                    Team B <span class="required-mark">*</span>
                                </label>
                                <select name="team_b_id" id="team_b_id" class="form-select" required>
                                    <option value="">-- Select Team B --</option>
                                    @foreach($teams as $team)
                                        <option value="{{ $team->id }}"
                                            {{ old('team_b_id') == $team->id ? 'selected' : '' }}>
                                            {{ $team->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Match Date --}}
                            <div class="col-md-6">
                                <label class="form-label fs-16 cs-label-color">Match Date & Time</label>
                                <input type="datetime-local" name="match_date" class="form-control"
                                       value="{{ old('match_date') }}">
                            </div>

                            {{-- Venue --}}
                            <div class="col-md-6">
                                <label class="form-label fs-16 cs-label-color">Venue</label>
                                <input type="text" name="venue" class="form-control"
                                       value="{{ old('venue') }}"
                                       placeholder="Ground / Stadium name">
                            </div>

                            {{-- Max Overs --}}
                            <div class="col-md-4">
                                <label class="form-label fs-16 cs-label-color">
                                    Max Overs <span class="required-mark">*</span>
                                </label>
                                <input type="number" name="max_overs" class="form-control"
                                       value="{{ old('max_overs') }}" min="1" placeholder="e.g. 20">
                            </div>

                            {{-- Bowler Max Overs --}}
                            <div class="col-md-4">
                                <label class="form-label fs-16 cs-label-color">Bowler Max Overs</label>
                                <input type="number" name="bowler_max_overs" class="form-control"
                                       value="{{ old('bowler_max_overs') }}" min="1"
                                       placeholder="e.g. 4 (leave blank for no limit)">
                            </div>

                            {{-- Status --}}
                            <div class="col-md-4">
                                <label class="form-label fs-16 cs-label-color">
                                    Status <span class="required-mark">*</span>
                                </label>
                                <select name="status" class="form-select" required>
                                    <option value="upcoming"  {{ old('status', 'upcoming') === 'upcoming'  ? 'selected' : '' }}>Upcoming</option>
                                    <option value="live"      {{ old('status') === 'live'      ? 'selected' : '' }}>Live</option>
                                    <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>

                        </div>{{-- /row --}}

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.cricket-matches.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left me-1"></i> Back to List
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-cricket me-1"></i> Create Match
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {

            const $matchType    = $('#match_type');
            const $tournField   = $('#tournament-field');
            const $tournSelect  = $('#tournament_id');
            const $teamA        = $('#team_a_id');
            const $teamB        = $('#team_b_id');

            // Show/hide tournament selector based on match type
            $matchType.on('change', function () {
                if ($(this).val() === 'tournament') {
                    $tournField.show();
                } else {
                    $tournField.hide();
                    $tournSelect.val('');
                }
            }).trigger('change'); // apply on page load for old() value

            // Prevent same team in both dropdowns
            function syncTeamOptions() {
                const selectedA = $teamA.val();
                const selectedB = $teamB.val();

                $teamB.find('option').each(function () {
                    $(this).prop('disabled', $(this).val() !== '' && $(this).val() === selectedA);
                });
                $teamA.find('option').each(function () {
                    $(this).prop('disabled', $(this).val() !== '' && $(this).val() === selectedB);
                });
            }

            $teamA.on('change', syncTeamOptions);
            $teamB.on('change', syncTeamOptions);
            syncTeamOptions();

            // Select2 for team dropdowns
            $('select[name="team_a_id"], select[name="team_b_id"], select[name="tournament_id"]').select2({
                width: '100%',
            });

        });
    </script>
@endpush
