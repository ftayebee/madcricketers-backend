<div class="modal fade" id="add-team" tabindex="-1" aria-labelledby="add-teamTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #002741;color: #fff;">
                <h5 class="modal-title" id="add-teamTitle">Add New Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.teams.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" style="background: #daf0ff;">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" name="name" class="form-control" id="">
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="form-group">
                                <label for="coach_name">Coach Name</label>
                                <input type="text" name="coach_name" class="form-control" id="">
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="form-group">
                                <label for="manager_name">Manager Name</label>
                                <input type="text" name="manager_name" class="form-control" id="">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="logo">Team Logo</label>
                                <input type="file" name="logo" class="form-control" id="">
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <div class="form-group">
                                <label for="logo">Assign Players</label>
                                <select class="form-control select2-hidden-accessible" id="player_selector2"
                                    name="player_ids[]" multiple="" style="width: 100%;" tabindex="-1" aria-hidden="true">
                                    @foreach (\App\Models\Player::all() as $player)
                                        <option value="{{ $player->id }}"
                                            data-img="{{ $player->user->image ?? '/default.png' }}"
                                            data-role="{{ $player->player_role }}"
                                            data-battingstyle="{{ $player->batting_style }}"
                                            data-bowlingstyle="{{ $player->bowling_style }}">
                                            {{ $player->user->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        const $playerSelector = $('#player_selector2').select2({
            placeholder: 'Search and Select Players...',
            dropdownParent: $('#add-team'),
            templateResult: formatPlayer,
            templateSelection: formatPlayerSelection,
            escapeMarkup: function(markup) {
                return markup;
            },
            closeOnSelect: false
        });

        function formatPlayer(player) {
            if (!player.id) return player.text;

            let img = $(player.element).data('img');
            let role = $(player.element).data('role');
            let battingstyle = $(player.element).data('battingstyle');
            let bowlingstyle = $(player.element).data('bowlingstyle');

            return `
                    <div style="display:flex; align-items:center; gap:10px;">
                        <img src="${img}" style="width:35px; height:35px; border-radius:50%; object-fit:cover;">
                        <div>
                            <div style="font-weight:600;">${player.text}</div>
                            <div style="font-size:12px; color:#666;">${role} | ${battingstyle} | ${bowlingstyle}</div>
                        </div>
                    </div>
                `;
        }

        function formatPlayerSelection(player) {
            if (!player.id) return player.text;

            let img = $(player.element).data('img');

            return `
                    <div style="display:flex; align-items:center; gap:8px;">
                        <img src="${img}" style="width:22px; height:22px; border-radius:50%; object-fit:cover;">
                        <span>${player.text}</span>
                    </div>
                `;
        }
    </script>
@endpush
