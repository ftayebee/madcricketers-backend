@extends('admin.layouts.theme')

@push('styles')
<style>
    /* ── Step indicator ──────────────────────────── */
    .step-indicator { display:flex; align-items:center; margin-bottom:2rem; }
    .step-bubble {
        width:36px; height:36px; border-radius:50%;
        display:flex; align-items:center; justify-content:center;
        font-weight:700; font-size:.9rem;
        border:2px solid #dee2e6; background:#fff; color:#6c757d;
        flex-shrink:0; transition:.25s;
    }
    .step-bubble.active  { border-color:#3787a1; background:#3787a1; color:#fff; }
    .step-bubble.done    { border-color:#198754; background:#198754; color:#fff; }
    .step-line { flex:1; height:2px; background:#dee2e6; margin:0 6px; transition:.25s; }
    .step-line.done      { background:#198754; }
    .step-label { font-size:.72rem; color:#6c757d; text-align:center; margin-top:4px; }
    .step-wrapper { display:flex; flex-direction:column; align-items:center; }

    /* ── Category cards ──────────────────────────── */
    .cat-card {
        border:2px solid #dee2e6; border-radius:10px; padding:1rem;
        cursor:pointer; text-align:center; transition:.2s;
        user-select:none;
    }
    .cat-card:hover { border-color:#3787a1; background:#f0f9fb; }
    .cat-card.selected { border-color:#3787a1; background:#e0f3f7; }
    .cat-card i { font-size:1.6rem; display:block; margin-bottom:.4rem; color:#3787a1; }

    /* ── Player cards in the list ────────────────── */
    .player-list {
        max-height:380px; overflow-y:auto;
        border:1px solid #dee2e6; border-radius:8px;
        padding:.4rem;
    }
    .player-item {
        display:flex; align-items:center; gap:.6rem;
        padding:.5rem .6rem; border-radius:6px; cursor:pointer;
        transition:.15s; border:1px solid transparent; margin-bottom:3px;
    }
    .player-item:hover  { background:#f8f9fa; }
    .player-item.selected { border-color:#3787a1; background:#e8f6fa; }
    .player-item.conflict { border-color:#dc3545 !important; background:#fff5f5 !important; }
    .player-avatar {
        width:38px; height:38px; border-radius:50%; object-fit:cover;
        flex-shrink:0; border:1px solid #dee2e6;
    }
    .player-name { font-weight:600; font-size:.88rem; line-height:1.2; }
    .player-meta { font-size:.75rem; color:#6c757d; }
    .player-check { margin-left:auto; font-size:1.1rem; color:#3787a1; display:none; }
    .player-item.selected .player-check { display:block; }
    .player-item.conflict .player-check { color:#dc3545; }

    /* ── Selected badge ──────────────────────────── */
    .selected-badge {
        display:inline-flex; align-items:center; gap:.3rem;
        background:#e0f3f7; border:1px solid #3787a1;
        border-radius:20px; padding:.2rem .6rem; font-size:.8rem; font-weight:600;
        color:#1a6a7c; margin-bottom:.5rem; cursor:pointer;
    }
    .selected-badge .remove-badge { color:#dc3545; font-weight:800; }

    /* ── Review summary ──────────────────────────── */
    .review-team-block { border:1px solid #dee2e6; border-radius:8px; padding:1rem; }
    .review-player-chip {
        display:inline-flex; align-items:center; gap:.3rem;
        background:#f8f9fa; border:1px solid #dee2e6;
        border-radius:20px; padding:.2rem .65rem; font-size:.8rem;
        margin:.2rem;
    }

    /* ── Misc ────────────────────────────────────── */
    .cs-label-color { color:#002450; }
    .required-mark  { color:red; font-size:16px; font-weight:800; }
    .section-hidden  { display:none; }

    /* married status radios */
    .married-radio-group label {
        border:2px solid #dee2e6; border-radius:8px; padding:.5rem 1.2rem;
        cursor:pointer; transition:.15s;
    }
    .married-radio-group input:checked + label {
        border-color:#3787a1; background:#e0f3f7; color:#1a6a7c; font-weight:600;
    }
</style>
@endpush

@section('content')

{{-- Validation errors (server-side) --}}
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ============================================================
     STEP INDICATOR
     ============================================================ --}}
<div class="step-indicator mb-4">
    @php
        $steps = ['Match Details', 'Category', 'Criteria', 'Select Players', 'Review'];
    @endphp
    @foreach($steps as $i => $label)
        @if($i > 0)
            <div class="step-line" id="line-{{ $i }}"></div>
        @endif
        <div class="step-wrapper">
            <div class="step-bubble {{ $i === 0 ? 'active' : '' }}" id="bubble-{{ $i + 1 }}">{{ $i + 1 }}</div>
            <div class="step-label">{{ $label }}</div>
        </div>
    @endforeach
</div>

{{-- ============================================================
     MAIN FORM  (all hidden inputs live here)
     ============================================================ --}}
<form id="casualMatchForm" action="{{ route('admin.cricket-matches.create-casual.store') }}" method="POST">
    @csrf

    {{-- Dynamic hidden inputs populated by JS --}}
    <input type="hidden" name="category"      id="hidden_category">
    <input type="hidden" name="team_a_value"  id="hidden_team_a_value">
    <input type="hidden" name="team_b_value"  id="hidden_team_b_value">
    {{-- team_a_players[] and team_b_players[] injected by JS before submit --}}

    {{-- ======================================================
         STEP 1 – Match Details
         ====================================================== --}}
    <div id="step-1" class="step-section">
        <div class="card custom-card-border">
            <div class="card-header bg-soft-cyan">
                <h5 class="text-center fs-20 fw-bold m-0">
                    <i class="ti ti-cricket me-2"></i>Step 1 — Match Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-12">
                        <label class="form-label cs-label-color">Match Title</label>
                        <input type="text" name="title" class="form-control"
                               value="{{ old('title') }}"
                               placeholder="Leave blank to auto-generate (Team A vs Team B)">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label cs-label-color">Match Date &amp; Time</label>
                        <input type="datetime-local" name="match_date" class="form-control"
                               value="{{ old('match_date') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label cs-label-color">Venue</label>
                        <input type="text" name="venue" class="form-control"
                               value="{{ old('venue') }}" placeholder="Ground / Stadium name">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label cs-label-color">
                            Max Overs <span class="required-mark">*</span>
                        </label>
                        <input type="number" name="max_overs" id="max_overs" class="form-control"
                               value="{{ old('max_overs', 10) }}" min="1" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label cs-label-color">Bowler Max Overs</label>
                        <input type="number" name="bowler_max_overs" class="form-control"
                               value="{{ old('bowler_max_overs') }}" min="1"
                               placeholder="e.g. 2 (blank = no limit)">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label cs-label-color">
                            Status <span class="required-mark">*</span>
                        </label>
                        <select name="status" class="form-select" required>
                            <option value="upcoming" {{ old('status','upcoming') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="live"     {{ old('status') === 'live'     ? 'selected' : '' }}>Live</option>
                            <option value="completed"{{ old('status') === 'completed'? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>

                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('admin.cricket-matches.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back to List
                </a>
                <button type="button" class="btn btn-primary" id="step1Next">
                    Next: Choose Category <i class="ti ti-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ======================================================
         STEP 2 – Category
         ====================================================== --}}
    <div id="step-2" class="step-section section-hidden">
        <div class="card custom-card-border">
            <div class="card-header bg-soft-cyan">
                <h5 class="text-center fs-20 fw-bold m-0">
                    <i class="ti ti-filter me-2"></i>Step 2 — How should teams be formed?
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted text-center mb-3">
                    Pick an attribute to split players into two rival teams.
                </p>
                <div class="row g-3 justify-content-center" id="categoryGrid">
                    @foreach($categories as $key => $label)
                        @php
                            $icons = [
                                'favourite_football_country'     => 'ti-ball-football',
                                'favourite_cricket_country'      => 'ti-cricket',
                                'favourite_football_league_team' => 'ti-shirt',
                                'married_status'                 => 'ti-heart',
                                'education_batch'                => 'ti-school',
                                'ssc_batch'                      => 'ti-certificate',
                            ];
                            $icon = $icons[$key] ?? 'ti-tag';
                        @endphp
                        <div class="col-6 col-md-4">
                            <div class="cat-card {{ old('category') === $key ? 'selected' : '' }}"
                                 data-category="{{ $key }}">
                                <i class="ti {{ $icon }}"></i>
                                <span class="fw-semibold">{{ $label }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div id="categoryError" class="text-danger text-center mt-2 d-none">
                    Please select a category.
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-go="1">
                    <i class="ti ti-arrow-left me-1"></i> Back
                </button>
                <button type="button" class="btn btn-primary" id="step2Next">
                    Next: Enter Criteria <i class="ti ti-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ======================================================
         STEP 3 – Team Criteria
         ====================================================== --}}
    <div id="step-3" class="step-section section-hidden">
        <div class="card custom-card-border">
            <div class="card-header bg-soft-cyan">
                <h5 class="text-center fs-20 fw-bold m-0" id="criteriaHeading">
                    <i class="ti ti-users me-2"></i>Step 3 — Team Criteria
                </h5>
            </div>
            <div class="card-body">
                <div id="criteriaBody">
                    {{-- injected by JS based on selected category --}}
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-go="2">
                    <i class="ti ti-arrow-left me-1"></i> Back
                </button>
                <button type="button" class="btn btn-primary" id="step3Next">
                    <i class="ti ti-search me-1"></i> Load Players
                </button>
            </div>
        </div>
    </div>

    {{-- ======================================================
         STEP 4 – Select Players
         ====================================================== --}}
    <div id="step-4" class="step-section section-hidden">
        <div class="card custom-card-border">
            <div class="card-header bg-soft-cyan">
                <h5 class="text-center fs-20 fw-bold m-0">
                    <i class="ti ti-users-group me-2"></i>Step 4 — Select Players
                </h5>
            </div>
            <div class="card-body">

                {{-- Loading spinner --}}
                <div id="playersLoading" class="text-center py-5 section-hidden">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Finding players…</p>
                </div>

                {{-- Conflict warning --}}
                <div id="conflictAlert" class="alert alert-danger d-none">
                    <i class="ti ti-alert-triangle me-1"></i>
                    <span id="conflictAlertText"></span>
                </div>

                {{-- Two-column player panels --}}
                <div id="playerPanels" class="row g-3">

                    {{-- Team A Panel --}}
                    <div class="col-12 col-md-6">
                        <div class="card border h-100">
                            <div class="card-header bg-primary bg-opacity-10 d-flex justify-content-between align-items-center">
                                <span class="fw-bold" id="teamAName">Team A</span>
                                <span class="badge bg-primary" id="teamACount">0 selected</span>
                            </div>
                            <div class="card-body p-2">
                                <input type="text" class="form-control form-control-sm mb-2"
                                       id="searchA" placeholder="Search players…">
                                <div class="player-list" id="listA">
                                    {{-- Populated by JS --}}
                                </div>
                            </div>
                            <div class="card-footer p-2">
                                <div id="selectedBadgesA" class="d-flex flex-wrap gap-1 mb-2"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary w-100"
                                        id="addOtherA" data-team="A">
                                    <i class="ti ti-user-plus me-1"></i> Add Other Player
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Team B Panel --}}
                    <div class="col-12 col-md-6">
                        <div class="card border h-100">
                            <div class="card-header bg-danger bg-opacity-10 d-flex justify-content-between align-items-center">
                                <span class="fw-bold" id="teamBName">Team B</span>
                                <span class="badge bg-danger" id="teamBCount">0 selected</span>
                            </div>
                            <div class="card-body p-2">
                                <input type="text" class="form-control form-control-sm mb-2"
                                       id="searchB" placeholder="Search players…">
                                <div class="player-list" id="listB">
                                    {{-- Populated by JS --}}
                                </div>
                            </div>
                            <div class="card-footer p-2">
                                <div id="selectedBadgesB" class="d-flex flex-wrap gap-1 mb-2"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary w-100"
                                        id="addOtherB" data-team="B">
                                    <i class="ti ti-user-plus me-1"></i> Add Other Player
                                </button>
                            </div>
                        </div>
                    </div>

                </div>{{-- /playerPanels --}}
            </div>
            <div class="card-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-go="3">
                    <i class="ti ti-arrow-left me-1"></i> Back
                </button>
                <button type="button" class="btn btn-primary" id="step4Next">
                    Review Match <i class="ti ti-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ======================================================
         STEP 5 – Review & Create
         ====================================================== --}}
    <div id="step-5" class="step-section section-hidden">
        <div class="card custom-card-border">
            <div class="card-header bg-soft-cyan">
                <h5 class="text-center fs-20 fw-bold m-0">
                    <i class="ti ti-clipboard-check me-2"></i>Step 5 — Review &amp; Create Match
                </h5>
            </div>
            <div class="card-body">

                {{-- Summary table --}}
                <table class="table table-bordered table-sm mb-3">
                    <tbody>
                        <tr><th style="width:200px">Category</th><td id="rev_category">—</td></tr>
                        <tr><th>Match Date</th>                  <td id="rev_date">—</td></tr>
                        <tr><th>Venue</th>                       <td id="rev_venue">—</td></tr>
                        <tr><th>Overs</th>                       <td id="rev_overs">—</td></tr>
                        <tr><th>Status</th>                      <td id="rev_status">—</td></tr>
                    </tbody>
                </table>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="review-team-block">
                            <h6 class="fw-bold text-primary" id="rev_teamAName">Team A</h6>
                            <div id="rev_teamAPlayers" class="d-flex flex-wrap"></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="review-team-block">
                            <h6 class="fw-bold text-danger" id="rev_teamBName">Team B</h6>
                            <div id="rev_teamBPlayers" class="d-flex flex-wrap"></div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="card-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-go="4">
                    <i class="ti ti-arrow-left me-1"></i> Back
                </button>
                <button type="submit" class="btn btn-success" id="submitBtn">
                    <i class="ti ti-cricket me-1"></i> Create Match
                </button>
            </div>
        </div>
    </div>

</form>{{-- /casualMatchForm --}}

{{-- ============================================================
     ADD OTHER PLAYER MODAL
     ============================================================ --}}
<div class="modal fade" id="addPlayerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-soft-cyan">
                <h5 class="modal-title fw-bold">Add Other Player</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <ul class="nav nav-tabs mb-3" id="addPlayerTabs">
                    <li class="nav-item">
                        <button class="nav-link active" data-tab="search">
                            <i class="ti ti-search me-1"></i> Search Existing
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-tab="create">
                            <i class="ti ti-user-plus me-1"></i> Create Guest
                        </button>
                    </li>
                </ul>

                {{-- Search tab --}}
                <div id="tabSearch">
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" id="searchExistingInput"
                               placeholder="Name or phone…">
                        <button class="btn btn-outline-primary" id="doSearchBtn">Search</button>
                    </div>
                    <div id="searchResults" class="player-list" style="max-height:220px;"></div>
                </div>

                {{-- Create tab --}}
                <div id="tabCreate" class="d-none">
                    <div class="mb-2">
                        <label class="form-label cs-label-color">
                            Full Name <span class="required-mark">*</span>
                        </label>
                        <input type="text" class="form-control" id="guestFullName"
                               placeholder="e.g. John Doe">
                    </div>
                    <div class="mb-2">
                        <label class="form-label cs-label-color">Phone (optional)</label>
                        <input type="text" class="form-control" id="guestPhone" placeholder="+880…">
                    </div>
                    <div class="row g-2">
                        <div class="col-4">
                            <label class="form-label cs-label-color">Role</label>
                            <select class="form-select form-select-sm" id="guestRole">
                                <option value="">—</option>
                                <option value="batsman">Batsman</option>
                                <option value="bowler">Bowler</option>
                                <option value="all-rounder">All-Rounder</option>
                                <option value="wicketkeeper">Wicketkeeper</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label cs-label-color">Batting</label>
                            <select class="form-select form-select-sm" id="guestBatting">
                                <option value="">—</option>
                                <option value="right-handed">Right</option>
                                <option value="left-handed">Left</option>
                                <option value="switch hitter">Switch</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label cs-label-color">Bowling</label>
                            <select class="form-select form-select-sm" id="guestBowling">
                                <option value="">—</option>
                                <option value="fast">Fast</option>
                                <option value="medium">Medium</option>
                                <option value="spin">Spin</option>
                                <option value="none">None</option>
                            </select>
                        </div>
                    </div>
                    <div id="createPlayerError" class="text-danger mt-2 d-none"></div>
                    <button type="button" class="btn btn-success mt-3 w-100" id="doCreateBtn">
                        <i class="ti ti-user-plus me-1"></i> Create &amp; Add to Team
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {

    /* ─────────────────────────────────────────────────────────────────
       STATE
    ───────────────────────────────────────────────────────────────── */
    let currentStep  = 1;
    let selCategory  = '{{ old("category", "") }}';
    let selAValue    = '';
    let selBValue    = '';
    let teamAName    = 'Team A';
    let teamBName    = 'Team B';
    let activeModal  = 'A'; // which team the modal is for

    const selectedA  = {}; // { playerId: playerObj }
    const selectedB  = {};

    const allPlayersA = []; // full list from API
    const allPlayersB = [];

    const CSRF = $('meta[name="csrf-token"]').attr('content')
             || $('input[name="_token"]').first().val();

    /* ─────────────────────────────────────────────────────────────────
       STEP NAVIGATION
    ───────────────────────────────────────────────────────────────── */
    function goTo(n) {
        currentStep = n;
        $('.step-section').addClass('section-hidden');
        $('#step-' + n).removeClass('section-hidden');
        updateIndicator();
        $('html, body').animate({ scrollTop: 0 }, 200);
    }

    function updateIndicator() {
        for (let i = 1; i <= 5; i++) {
            const $b = $('#bubble-' + i);
            $b.removeClass('active done');
            if (i < currentStep)       $b.addClass('done').html('<i class="ti ti-check" style="font-size:.8rem"></i>');
            else if (i === currentStep) $b.addClass('active').text(i);
            else                        $b.text(i);

            if (i < 5) {
                $('#line-' + i).toggleClass('done', i < currentStep);
            }
        }
    }

    // Generic "Back" buttons
    $('[data-go]').on('click', function () { goTo(parseInt($(this).data('go'))); });

    /* ─────────────────────────────────────────────────────────────────
       STEP 1 → 2
    ───────────────────────────────────────────────────────────────── */
    $('#step1Next').on('click', function () {
        const overs = $('#max_overs').val();
        if (!overs || parseInt(overs) < 1) {
            alert('Please enter valid Max Overs before continuing.');
            return;
        }
        goTo(2);
    });

    /* ─────────────────────────────────────────────────────────────────
       STEP 2 – Category selection
    ───────────────────────────────────────────────────────────────── */
    $(document).on('click', '.cat-card', function () {
        $('.cat-card').removeClass('selected');
        $(this).addClass('selected');
        selCategory = $(this).data('category');
        $('#categoryError').addClass('d-none');
    });

    // Restore old value on page load (validation failure)
    if (selCategory) {
        $('[data-category="' + selCategory + '"]').addClass('selected');
    }

    $('#step2Next').on('click', function () {
        if (!selCategory) {
            $('#categoryError').removeClass('d-none');
            return;
        }
        buildCriteriaUI();
        goTo(3);
    });

    /* ─────────────────────────────────────────────────────────────────
       STEP 3 – Build criteria UI based on category
    ───────────────────────────────────────────────────────────────── */
    const categoryMeta = {
        favourite_football_country:     { label: 'Favourite Football Country', type: 'text',    placeholder: 'e.g. Brazil / Argentina' },
        favourite_cricket_country:      { label: 'Favourite Cricket Country',   type: 'text',    placeholder: 'e.g. India / Bangladesh'  },
        favourite_football_league_team: { label: 'Favourite League Team',       type: 'text',    placeholder: 'e.g. FCB / RMA'           },
        married_status:                 { label: 'Married Status',              type: 'married'                                           },
        education_batch:                { label: 'Education Batch',             type: 'batch',   placeholder: 'e.g. 2015-2017'           },
        ssc_batch:                      { label: 'SSC Batch',                   type: 'year',    placeholder: 'e.g. 2016'                },
    };

    function buildCriteriaUI() {
        const meta = categoryMeta[selCategory] || { label: selCategory, type: 'text', placeholder: '' };
        $('#criteriaHeading').html('<i class="ti ti-users me-2"></i>Step 3 — ' + meta.label);

        let html = '<div class="row g-3">';

        if (meta.type === 'married') {
            html += criteriaMarried('A');
            html += criteriaMarried('B');
        } else {
            html += criteriaText('A', meta.placeholder);
            html += criteriaText('B', meta.placeholder);
        }

        html += '</div>';
        $('#criteriaBody').html(html);
    }

    function criteriaText(team, placeholder) {
        const color = team === 'A' ? 'text-primary' : 'text-danger';
        return `
            <div class="col-12 col-md-6">
                <div class="card border p-3">
                    <h6 class="fw-bold ${color}"><i class="ti ti-users me-1"></i>Team ${team} Value</h6>
                    <input type="text" id="input${team}" class="form-control"
                           placeholder="${placeholder}" value="">
                    <small class="text-muted mt-1">Players matching this value will be shown for Team ${team}.</small>
                </div>
            </div>`;
    }

    function criteriaMarried(team) {
        const color = team === 'A' ? 'text-primary' : 'text-danger';
        return `
            <div class="col-12 col-md-6">
                <div class="card border p-3">
                    <h6 class="fw-bold ${color}"><i class="ti ti-heart me-1"></i>Team ${team} — Married Status</h6>
                    <div class="married-radio-group d-flex gap-2 flex-wrap mt-1">
                        <div>
                            <input type="radio" name="married${team}" id="m${team}married"
                                   value="married" class="d-none">
                            <label for="m${team}married">💍 Married</label>
                        </div>
                        <div>
                            <input type="radio" name="married${team}" id="m${team}unmarried"
                                   value="unmarried" class="d-none">
                            <label for="m${team}unmarried">🙂 Unmarried / Single</label>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    function getCriteriaValues() {
        const meta = categoryMeta[selCategory] || {};
        if (meta.type === 'married') {
            return {
                a: $('input[name="marriedA"]:checked').val() || '',
                b: $('input[name="marriedB"]:checked').val() || '',
            };
        }
        return {
            a: $('#inputA').val().trim(),
            b: $('#inputB').val().trim(),
        };
    }

    /* ─────────────────────────────────────────────────────────────────
       STEP 3 → 4: Load players via AJAX
    ───────────────────────────────────────────────────────────────── */
    $('#step3Next').on('click', function () {
        const vals = getCriteriaValues();
        if (!vals.a || !vals.b) {
            alert('Please fill in values for both Team A and Team B.');
            return;
        }
        selAValue = vals.a;
        selBValue = vals.b;

        // Show step 4 and trigger load
        goTo(4);
        loadPlayers();
    });

    function loadPlayers() {
        $('#playersLoading').removeClass('section-hidden');
        $('#playerPanels').addClass('section-hidden');

        $.ajax({
            url: '{{ route("admin.cricket-matches.create-casual.filter-players") }}',
            method: 'POST',
            data: {
                _token:       CSRF,
                category:     selCategory,
                team_a_value: selAValue,
                team_b_value: selBValue,
            },
            success: function (res) {
                $('#playersLoading').addClass('section-hidden');
                $('#playerPanels').removeClass('section-hidden');

                teamAName = res.team_a_name || 'Team A';
                teamBName = res.team_b_name || 'Team B';
                $('#teamAName').text(teamAName);
                $('#teamBName').text(teamBName);

                // Reset and fill player arrays
                allPlayersA.length = 0;
                allPlayersB.length = 0;
                res.team_a_players.forEach(p => allPlayersA.push(p));
                res.team_b_players.forEach(p => allPlayersB.push(p));

                renderList('A', allPlayersA);
                renderList('B', allPlayersB);
            },
            error: function (xhr) {
                $('#playersLoading').addClass('section-hidden');
                const msg = xhr.responseJSON?.errors
                    ? Object.values(xhr.responseJSON.errors).flat().join('\n')
                    : 'Failed to load players. Please try again.';
                alert(msg);
                goTo(3);
            }
        });
    }

    /* ─────────────────────────────────────────────────────────────────
       PLAYER LIST RENDERING
    ───────────────────────────────────────────────────────────────── */
    const defaultAvatar = '{{ asset("storage/assets/images/users/dummy-avatar.jpg") }}';

    function renderList(team, players, filter) {
        const $list = team === 'A' ? $('#listA') : $('#listB');
        const sel   = team === 'A' ? selectedA   : selectedB;
        const otherSel = team === 'A' ? selectedB : selectedA;

        filter = (filter || '').toLowerCase();

        const visible = players.filter(p =>
            !filter || p.full_name.toLowerCase().includes(filter)
        );

        if (visible.length === 0) {
            $list.html('<p class="text-center text-muted py-3 mb-0">No players found.</p>');
            return;
        }

        let html = '';
        visible.forEach(p => {
            const isSelected = !!sel[p.id];
            const isConflict = isSelected && !!otherSel[p.id];
            const avatar     = p.image || defaultAvatar;
            const meta       = [p.player_role, p.batting_style].filter(Boolean).join(' · ') || 'No stats';
            const badge      = p.player_type === 'guest'
                ? '<span class="badge bg-secondary ms-1" style="font-size:.65rem">guest</span>' : '';

            html += `
                <div class="player-item ${isSelected ? 'selected' : ''} ${isConflict ? 'conflict' : ''}"
                     data-id="${p.id}" data-team="${team}">
                    <img src="${avatar}" alt="${p.full_name}" class="player-avatar"
                         onerror="this.src='${defaultAvatar}'">
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="player-name text-truncate">${p.full_name}${badge}</div>
                        <div class="player-meta">${meta}</div>
                    </div>
                    <i class="ti ti-circle-check player-check"></i>
                </div>`;
        });

        $list.html(html);
    }

    /* click to select / deselect */
    $(document).on('click', '.player-item', function () {
        const team = $(this).data('team');
        const id   = $(this).data('id');
        const sel  = team === 'A' ? selectedA : selectedB;
        const players = team === 'A' ? allPlayersA : allPlayersB;
        const playerObj = players.find(p => p.id == id);
        if (!playerObj) return;

        if (sel[id]) {
            delete sel[id];
        } else {
            sel[id] = playerObj;
        }
        refreshPanel(team);
        checkConflicts();
    });

    function refreshPanel(team) {
        const players = team === 'A' ? allPlayersA : allPlayersB;
        const filter  = (team === 'A' ? $('#searchA') : $('#searchB')).val();
        renderList(team, players, filter);
        updateBadges(team);
        updateCount(team);
    }

    function updateCount(team) {
        const count = Object.keys(team === 'A' ? selectedA : selectedB).length;
        const $badge = team === 'A' ? $('#teamACount') : $('#teamBCount');
        $badge.text(count + ' selected');
    }

    function updateBadges(team) {
        const sel   = team === 'A' ? selectedA : selectedB;
        const $wrap = team === 'A' ? $('#selectedBadgesA') : $('#selectedBadgesB');
        let html = '';
        Object.values(sel).forEach(p => {
            html += `<span class="selected-badge" data-id="${p.id}" data-team="${team}">
                        ${p.full_name}
                        <span class="remove-badge">✕</span>
                    </span>`;
        });
        $wrap.html(html);
    }

    /* Remove via badge */
    $(document).on('click', '.remove-badge', function () {
        const $badge = $(this).closest('.selected-badge');
        const team   = $badge.data('team');
        const id     = $badge.data('id');
        const sel    = team === 'A' ? selectedA : selectedB;
        delete sel[id];
        refreshPanel(team);
        checkConflicts();
    });

    /* Search filter */
    $('#searchA').on('input', function () { renderList('A', allPlayersA, $(this).val()); });
    $('#searchB').on('input', function () { renderList('B', allPlayersB, $(this).val()); });

    /* Conflict detection */
    function checkConflicts() {
        const conflicts = Object.keys(selectedA).filter(id => selectedB[id]);
        if (conflicts.length) {
            const names = conflicts.map(id => selectedA[id].full_name).join(', ');
            $('#conflictAlert').removeClass('d-none');
            $('#conflictAlertText').text('Conflict: ' + names + ' — same player in both teams!');
        } else {
            $('#conflictAlert').addClass('d-none');
        }
    }

    /* ─────────────────────────────────────────────────────────────────
       STEP 4 → 5: Review
    ───────────────────────────────────────────────────────────────── */
    $('#step4Next').on('click', function () {
        if (Object.keys(selectedA).length === 0) {
            alert('Please select at least one player for Team A.'); return;
        }
        if (Object.keys(selectedB).length === 0) {
            alert('Please select at least one player for Team B.'); return;
        }
        const conflicts = Object.keys(selectedA).filter(id => selectedB[id]);
        if (conflicts.length) {
            alert('Remove the conflicting players before continuing.'); return;
        }
        buildReview();
        goTo(5);
    });

    function buildReview() {
        const catMeta  = categoryMeta[selCategory] || {};
        const overs    = $('input[name="max_overs"]').val();
        const status   = $('select[name="status"]').val();
        const date     = $('input[name="match_date"]').val();
        const venue    = $('input[name="venue"]').val();

        $('#rev_category').text(catMeta.label || selCategory);
        $('#rev_date').text(date ? new Date(date).toLocaleString() : '—');
        $('#rev_venue').text(venue || '—');
        $('#rev_overs').text(overs + ' overs');
        $('#rev_status').text(status.charAt(0).toUpperCase() + status.slice(1));

        $('#rev_teamAName').text(teamAName);
        $('#rev_teamBName').text(teamBName);

        $('#rev_teamAPlayers').html(
            Object.values(selectedA).map(p =>
                `<span class="review-player-chip"><i class="ti ti-user" style="font-size:.75rem"></i>${p.full_name}</span>`
            ).join('')
        );
        $('#rev_teamBPlayers').html(
            Object.values(selectedB).map(p =>
                `<span class="review-player-chip"><i class="ti ti-user" style="font-size:.75rem"></i>${p.full_name}</span>`
            ).join('')
        );
    }

    /* ─────────────────────────────────────────────────────────────────
       FORM SUBMIT – inject hidden inputs
    ───────────────────────────────────────────────────────────────── */
    $('#casualMatchForm').on('submit', function () {
        // Set hidden fields
        $('#hidden_category').val(selCategory);
        $('#hidden_team_a_value').val(selAValue);
        $('#hidden_team_b_value').val(selBValue);

        // Remove old player inputs
        $('input[name="team_a_players[]"], input[name="team_b_players[]"]').remove();

        const $form = $(this);
        Object.keys(selectedA).forEach(id => {
            $form.append($('<input>').attr({ type:'hidden', name:'team_a_players[]', value: id }));
        });
        Object.keys(selectedB).forEach(id => {
            $form.append($('<input>').attr({ type:'hidden', name:'team_b_players[]', value: id }));
        });

        $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Creating…');
    });

    /* ─────────────────────────────────────────────────────────────────
       ADD OTHER PLAYER MODAL
    ───────────────────────────────────────────────────────────────── */
    $('#addOtherA, #addOtherB').on('click', function () {
        activeModal = $(this).data('team');
        resetModal();
        new bootstrap.Modal(document.getElementById('addPlayerModal')).show();
    });

    /* Tab switching */
    $('#addPlayerTabs .nav-link').on('click', function () {
        $('#addPlayerTabs .nav-link').removeClass('active');
        $(this).addClass('active');
        const tab = $(this).data('tab');
        if (tab === 'search') {
            $('#tabSearch').removeClass('d-none');
            $('#tabCreate').addClass('d-none');
        } else {
            $('#tabSearch').addClass('d-none');
            $('#tabCreate').removeClass('d-none');
        }
    });

    function resetModal() {
        $('#searchExistingInput').val('');
        $('#searchResults').html('');
        $('#guestFullName, #guestPhone').val('');
        $('#guestRole, #guestBatting, #guestBowling').val('');
        $('#createPlayerError').addClass('d-none').text('');
        // Switch to search tab
        $('#addPlayerTabs .nav-link').removeClass('active').first().addClass('active');
        $('#tabSearch').removeClass('d-none');
        $('#tabCreate').addClass('d-none');
    }

    /* Search existing player */
    $('#doSearchBtn').on('click', function () {
        const q = $('#searchExistingInput').val().trim();
        if (!q) return;
        $('#searchResults').html('<div class="text-center py-2"><div class="spinner-border spinner-border-sm"></div></div>');

        $.ajax({
            url: '{{ route("admin.cricket-matches.create-casual.add-player") }}',
            method: 'POST',
            data: { _token: CSRF, search_query: q },
            success: function (res) {
                if (!res.players || res.players.length === 0) {
                    $('#searchResults').html('<p class="text-center text-muted py-2 mb-0">No players found.</p>');
                    return;
                }
                let html = '';
                res.players.forEach(p => {
                    const avatar = p.image || defaultAvatar;
                    html += `
                        <div class="player-item" data-id="${p.id}" data-modal-team="${activeModal}"
                             data-player='${JSON.stringify(p).replace(/'/g,"&#39;")}'>
                            <img src="${avatar}" class="player-avatar" onerror="this.src='${defaultAvatar}'">
                            <div>
                                <div class="player-name">${p.full_name}</div>
                                <div class="player-meta">${p.player_role || '—'}</div>
                            </div>
                            <button class="btn btn-sm btn-outline-primary ms-auto pick-player-btn">Add</button>
                        </div>`;
                });
                $('#searchResults').html(html);
            },
            error: function () {
                $('#searchResults').html('<p class="text-danger text-center py-2 mb-0">Error searching. Try again.</p>');
            }
        });
    });

    /* Also search on Enter key */
    $('#searchExistingInput').on('keypress', function (e) {
        if (e.which === 13) { e.preventDefault(); $('#doSearchBtn').trigger('click'); }
    });

    /* Pick a player from search results */
    $(document).on('click', '.pick-player-btn', function () {
        const $item   = $(this).closest('.player-item');
        const team    = $item.data('modal-team');
        const player  = JSON.parse($item.attr('data-player'));
        addPlayerToTeam(team, player);
        bootstrap.Modal.getInstance(document.getElementById('addPlayerModal')).hide();
    });

    /* Create guest player */
    $('#doCreateBtn').on('click', function () {
        const fullName = $('#guestFullName').val().trim();
        if (!fullName) {
            $('#createPlayerError').removeClass('d-none').text('Full name is required.');
            return;
        }
        $('#doCreateBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Creating…');
        $('#createPlayerError').addClass('d-none');

        $.ajax({
            url: '{{ route("admin.cricket-matches.create-casual.add-player") }}',
            method: 'POST',
            data: {
                _token:         CSRF,
                full_name:      fullName,
                phone:          $('#guestPhone').val().trim(),
                player_role:    $('#guestRole').val(),
                batting_style:  $('#guestBatting').val(),
                bowling_style:  $('#guestBowling').val(),
            },
            success: function (res) {
                addPlayerToTeam(activeModal, res.player);
                bootstrap.Modal.getInstance(document.getElementById('addPlayerModal')).hide();
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Error creating player.';
                $('#createPlayerError').removeClass('d-none').text(msg);
                $('#doCreateBtn').prop('disabled', false).html('<i class="ti ti-user-plus me-1"></i> Create &amp; Add to Team');
            }
        });
    });

    /* Common: add player obj to a team's selected set + re-render */
    function addPlayerToTeam(team, player) {
        const id = player.id;
        if (team === 'A') {
            selectedA[id] = player;
            if (!allPlayersA.find(p => p.id == id)) allPlayersA.push(player);
        } else {
            selectedB[id] = player;
            if (!allPlayersB.find(p => p.id == id)) allPlayersB.push(player);
        }
        refreshPanel(team);
        checkConflicts();
    }

    /* ─────────────────────────────────────────────────────────────────
       Init
    ───────────────────────────────────────────────────────────────── */
    updateIndicator();

    // If validation failed server-side, restore state from old() values
    @if(old('category'))
        selCategory = '{{ old("category") }}';
        selAValue   = '{{ old("team_a_value") }}';
        selBValue   = '{{ old("team_b_value") }}';
        buildCriteriaUI();
        goTo(2); // go back to category step so admin can re-select
    @endif

});
</script>
@endpush
