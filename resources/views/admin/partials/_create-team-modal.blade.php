@php
    // Eager-load relationships once so the modal renders fast and avoids
    // N+1 queries on Player->user. This data powers both the main player grid
    // and the filter dropdowns below.
    $modalPlayers = \App\Models\Player::with('user')->get();

    // Helper: unique non-empty values for a given attribute, sorted.
    $uniqueValues = function ($attribute) use ($modalPlayers) {
        return $modalPlayers
            ->pluck($attribute)
            ->filter(fn($v) => $v !== null && $v !== '')
            ->unique()
            ->sort()
            ->values();
    };

    $filterRoles        = $uniqueValues('player_role');
    $filterBatting      = $uniqueValues('batting_style');
    $filterBowling      = $uniqueValues('bowling_style');
    $filterSscBatch     = $uniqueValues('ssc_batch');
    $filterEduBatch     = $uniqueValues('education_batch');
    $filterMarried      = $uniqueValues('married_status');
    $filterFavCricket   = $uniqueValues('favourite_cricket_country');
    $filterFavFootball  = $uniqueValues('favourite_football_country');
    $filterFavLeague    = $uniqueValues('favourite_football_league_team');
@endphp

<div class="modal fade" id="add-team" tabindex="-1" aria-labelledby="add-teamTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: #002741;color: #fff;">
                <h5 class="modal-title" id="add-teamTitle">Add New Team</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.teams.store') }}" method="post" enctype="multipart/form-data" id="createTeamForm">
                @csrf
                <div class="modal-body" style="background: #daf0ff;">
                    {{-- ---------- Basic team info ---------- --}}
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="team_name_input" class="form-label">Team Name</label>
                            <input type="text" name="name" class="form-control" id="team_name_input" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Coach Name</label>
                            <input type="text" name="coach_name" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Manager Name</label>
                            <input type="text" name="manager_name" class="form-control">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Team Logo</label>
                            <input type="file" name="logo" class="form-control">
                        </div>
                    </div>

                    {{-- ---------- Quick Player Filter Bar ---------- --}}
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white d-flex flex-wrap align-items-center gap-2 justify-content-between">
                            <div class="fw-bold">
                                <i class="fa fa-filter me-1"></i> Filter Players
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="badge bg-info" id="ctm-visible-count">0 visible</span>
                                <span class="badge bg-success" id="ctm-selected-count">0 selected</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="ctm-reset-filters">
                                    <i class="fa fa-times me-1"></i>Reset
                                </button>
                            </div>
                        </div>
                        <div class="card-body pt-2">
                            <div class="row g-2">
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label small mb-1">Search by Name</label>
                                    <input type="text" class="form-control form-control-sm" id="ctm-filter-name" placeholder="Type name…">
                                </div>
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label small mb-1">Role</label>
                                    <select class="form-select form-select-sm ctm-filter" data-attr="role">
                                        <option value="">Any</option>
                                        @foreach ($filterRoles as $v)
                                            <option value="{{ $v }}">{{ ucfirst($v) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label small mb-1">Batting Style</label>
                                    <select class="form-select form-select-sm ctm-filter" data-attr="battingstyle">
                                        <option value="">Any</option>
                                        @foreach ($filterBatting as $v)
                                            <option value="{{ $v }}">{{ ucfirst($v) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label small mb-1">Bowling Style</label>
                                    <select class="form-select form-select-sm ctm-filter" data-attr="bowlingstyle">
                                        <option value="">Any</option>
                                        @foreach ($filterBowling as $v)
                                            <option value="{{ $v }}">{{ ucfirst($v) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label small mb-1">SSC Batch</label>
                                    <select class="form-select form-select-sm ctm-filter" data-attr="sscbatch">
                                        <option value="">Any</option>
                                        @foreach ($filterSscBatch as $v)
                                            <option value="{{ $v }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label small mb-1">Education Batch</label>
                                    <select class="form-select form-select-sm ctm-filter" data-attr="edubatch">
                                        <option value="">Any</option>
                                        @foreach ($filterEduBatch as $v)
                                            <option value="{{ $v }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label small mb-1">Marital Status</label>
                                    <select class="form-select form-select-sm ctm-filter" data-attr="married">
                                        <option value="">Any</option>
                                        @foreach ($filterMarried as $v)
                                            <option value="{{ $v }}">{{ ucfirst($v) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label small mb-1">Fav. Cricket Country</label>
                                    <select class="form-select form-select-sm ctm-filter" data-attr="favcricket">
                                        <option value="">Any</option>
                                        @foreach ($filterFavCricket as $v)
                                            <option value="{{ $v }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label small mb-1">Fav. Football Country</label>
                                    <select class="form-select form-select-sm ctm-filter" data-attr="favfootball">
                                        <option value="">Any</option>
                                        @foreach ($filterFavFootball as $v)
                                            <option value="{{ $v }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label small mb-1">Fav. League Team</label>
                                    <select class="form-select form-select-sm ctm-filter" data-attr="favleague">
                                        <option value="">Any</option>
                                        @foreach ($filterFavLeague as $v)
                                            <option value="{{ $v }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Bulk action row --}}
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                                <button type="button" class="btn btn-sm btn-primary" id="ctm-select-all-visible">
                                    <i class="fa fa-check-double me-1"></i>Select All Visible
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="ctm-clear-selection">
                                    <i class="fa fa-eraser me-1"></i>Clear Selection
                                </button>
                                <div class="vr mx-1"></div>
                                <div class="input-group input-group-sm" style="max-width: 230px;">
                                    <span class="input-group-text">Pick Random</span>
                                    <input type="number" class="form-control" id="ctm-random-count" min="1" value="11">
                                    <button class="btn btn-outline-secondary" type="button" id="ctm-pick-random">from visible</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ---------- Player Grid ---------- --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-2" style="max-height: 340px; overflow-y: auto; background:#fff;">
                            <div class="row g-2" id="ctm-player-grid">
                                @foreach ($modalPlayers as $player)
                                    @php
                                        $img = $player->user->image ?? '/default.png';
                                        $name = $player->user->full_name ?? 'Unknown';
                                    @endphp
                                    <div class="col-md-6 col-lg-4 ctm-player-card"
                                        data-id="{{ $player->id }}"
                                        data-name="{{ strtolower($name) }}"
                                        data-role="{{ $player->player_role }}"
                                        data-battingstyle="{{ $player->batting_style }}"
                                        data-bowlingstyle="{{ $player->bowling_style }}"
                                        data-sscbatch="{{ $player->ssc_batch }}"
                                        data-edubatch="{{ $player->education_batch }}"
                                        data-married="{{ $player->married_status }}"
                                        data-favcricket="{{ $player->favourite_cricket_country }}"
                                        data-favfootball="{{ $player->favourite_football_country }}"
                                        data-favleague="{{ $player->favourite_football_league_team }}">
                                        <label class="d-flex align-items-center gap-2 border rounded p-2 w-100 mb-0 ctm-player-label" style="cursor:pointer;">
                                            <input type="checkbox" class="form-check-input ctm-player-check" value="{{ $player->id }}">
                                            <img src="{{ $img }}" onerror="this.src='/default.png'"
                                                alt=""
                                                style="width:34px;height:34px;border-radius:50%;object-fit:cover;">
                                            <div class="flex-grow-1 small">
                                                <div class="fw-semibold text-dark">{{ $name }}</div>
                                                <div class="text-muted" style="font-size:11px;">
                                                    {{ $player->player_role ?? '—' }}
                                                    @if($player->ssc_batch) · SSC {{ $player->ssc_batch }} @endif
                                                    @if($player->married_status) · {{ ucfirst($player->married_status) }} @endif
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div id="ctm-empty-state" class="text-center text-muted py-4 d-none">
                                No players match the selected filters.
                            </div>
                        </div>
                    </div>

                    {{-- Hidden inputs: on submit we mirror the selected checkboxes into an array
                         the existing TeamController@store already reads via $request->input('player_ids'). --}}
                    <div id="ctm-hidden-inputs"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i>Create Team
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ---------- Filter + selection logic (scoped to #add-team) ---------- --}}
<script>
(function () {
    // Guard: avoid double init if this partial is included on multiple pages.
    if (window.__ctmInit) return;
    window.__ctmInit = true;

    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('add-team');
        if (!modal) return;

        const grid            = modal.querySelector('#ctm-player-grid');
        const cards           = Array.from(modal.querySelectorAll('.ctm-player-card'));
        const filterSelects   = Array.from(modal.querySelectorAll('.ctm-filter'));
        const nameInput       = modal.querySelector('#ctm-filter-name');
        const emptyState      = modal.querySelector('#ctm-empty-state');
        const visibleCountEl  = modal.querySelector('#ctm-visible-count');
        const selectedCountEl = modal.querySelector('#ctm-selected-count');
        const hiddenInputsEl  = modal.querySelector('#ctm-hidden-inputs');
        const form            = modal.querySelector('#createTeamForm');

        const getFilters = () => {
            const f = { name: (nameInput.value || '').trim().toLowerCase() };
            filterSelects.forEach(sel => {
                f[sel.dataset.attr] = sel.value;
            });
            return f;
        };

        const cardMatches = (card, f) => {
            if (f.name && !card.dataset.name.includes(f.name)) return false;
            for (const key in f) {
                if (key === 'name') continue;
                if (f[key] && (card.dataset[key] || '') !== f[key]) return false;
            }
            return true;
        };

        const applyFilters = () => {
            const f = getFilters();
            let visible = 0;
            cards.forEach(card => {
                const show = cardMatches(card, f);
                card.classList.toggle('d-none', !show);
                if (show) visible++;
            });
            visibleCountEl.textContent = visible + ' visible';
            emptyState.classList.toggle('d-none', visible !== 0);
        };

        const updateSelectedCount = () => {
            const n = modal.querySelectorAll('.ctm-player-check:checked').length;
            selectedCountEl.textContent = n + ' selected';
        };

        // Wire up filter inputs
        filterSelects.forEach(sel => sel.addEventListener('change', applyFilters));
        nameInput.addEventListener('input', applyFilters);

        // Reset
        modal.querySelector('#ctm-reset-filters').addEventListener('click', () => {
            filterSelects.forEach(sel => sel.value = '');
            nameInput.value = '';
            applyFilters();
        });

        // Select all currently visible
        modal.querySelector('#ctm-select-all-visible').addEventListener('click', () => {
            cards.forEach(card => {
                if (!card.classList.contains('d-none')) {
                    card.querySelector('.ctm-player-check').checked = true;
                }
            });
            updateSelectedCount();
        });

        // Clear selection entirely
        modal.querySelector('#ctm-clear-selection').addEventListener('click', () => {
            cards.forEach(card => card.querySelector('.ctm-player-check').checked = false);
            updateSelectedCount();
        });

        // Pick random N from visible (unchecks non-chosen visible ones for clarity)
        modal.querySelector('#ctm-pick-random').addEventListener('click', () => {
            const n = parseInt(modal.querySelector('#ctm-random-count').value, 10) || 0;
            const visibleCards = cards.filter(c => !c.classList.contains('d-none'));
            // Fisher–Yates shuffle
            for (let i = visibleCards.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [visibleCards[i], visibleCards[j]] = [visibleCards[j], visibleCards[i]];
            }
            const picked = visibleCards.slice(0, n);
            visibleCards.forEach(c => c.querySelector('.ctm-player-check').checked = false);
            picked.forEach(c => c.querySelector('.ctm-player-check').checked = true);
            updateSelectedCount();
        });

        // Track checkbox changes
        grid.addEventListener('change', (e) => {
            if (e.target.classList.contains('ctm-player-check')) updateSelectedCount();
        });

        // On submit, serialize selected checkboxes into player_ids[] hidden inputs
        // so existing TeamController@store handles them unchanged.
        form.addEventListener('submit', () => {
            hiddenInputsEl.innerHTML = '';
            modal.querySelectorAll('.ctm-player-check:checked').forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'player_ids[]';
                input.value = cb.value;
                hiddenInputsEl.appendChild(input);
            });
        });

        // Initial render
        applyFilters();
        updateSelectedCount();
    });
})();
</script>

<style>
    #add-team .ctm-player-card .ctm-player-label:hover { background: #f5faff; }
    #add-team .ctm-player-check:checked + img + div { color: #0d6efd; }
    #add-team .ctm-player-card .ctm-player-check:checked ~ *,
    #add-team .ctm-player-card:has(.ctm-player-check:checked) .ctm-player-label {
        background: #e7f1ff; border-color: #0d6efd;
    }
</style>
