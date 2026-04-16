@extends('admin.layouts.theme')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card custom-card-border">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Teams List</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-team">
                        Add New
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12 table-responsive">
                            <table class="table datatable no-footer" id="tbl-players">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Coach</th>
                                        <th>Manager</th>
                                        <th>Total Players</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.partials._create-team-modal')

    <div class="modal fade" id="assignPlayerModal" tabindex="-1" aria-labelledby="assignPlayerModalLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignPlayerModalLabel">Assign Players to Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="assignPlayerForm">
                    <div class="modal-body">
                        <input type="hidden" name="team_id" id="modal_team_id" value="11">

                        <div class="mb-3">
                            <label for="player_selector" class="form-label">Select Players</label>
                            <select class="form-control" id="player_selector" name="player_ids[]"
                                multiple style="width: 100%;">
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Assign Players</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            function formatPlayer(player) {
                if (!player.id) return player.text;
                let img = $(player.element).data('img');
                let role = $(player.element).data('role') || 'Player';
                let battingstyle = $(player.element).data('battingstyle') || '';
                let bowlingstyle = $(player.element).data('bowlingstyle') || '';

                return `
                    <div style="display:flex; align-items:center; gap:10px;">
                        <img src="${img}" onerror="this.src='/default.png'" style="width:35px; height:35px; border-radius:50%; object-fit:cover;">
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
                        <img src="${img}" onerror="this.src='/default.png'" style="width:22px; height:22px; border-radius:50%; object-fit:cover;">
                        <span>${player.text}</span>
                    </div>
                `;
            }

            try {
                window.$playerSelector = $('#player_selector').select2({
                    placeholder: 'Search and select players...',
                    dropdownParent: $('#assignPlayerModal'),
                    width: '100%',
                    templateResult: formatPlayer,
                    templateSelection: formatPlayerSelection,
                    escapeMarkup: function(markup) { return markup; }
                });
            } catch (e) {
                console.error("Select2 Init Error Modal 1: ", e);
            }

            // The #player_selector2 control was replaced with a filter-based
            // player grid inside _create-team-modal.blade.php. This block is
            // intentionally left as a no-op for backwards compatibility with
            // any template that still references $playerSelector2.
            if ($('#player_selector2').length) {
                try {
                    window.$playerSelector2 = $('#player_selector2').select2({
                        placeholder: 'Search and select players...',
                        dropdownParent: $('#add-team'),
                        width: '100%',
                        templateResult: formatPlayer,
                        templateSelection: formatPlayerSelection,
                        escapeMarkup: function(markup) { return markup; }
                    });
                } catch (e) {
                    console.error("Select2 Init Error Modal 2: ", e);
                }
            }
        });

        $(document).ready(function() {
            const redirectTo = "{{ url()->current() }}"
            let teamTable = $('#tbl-players').DataTable({
                processing: false,
                serverSide: false,
                searching: true,
                responsive: false,
                autoWidth: false,
                destroy: true,
                ajax: {
                    url: '{{ route('admin.teams.loader') }}',
                    type: 'GET',
                    dataSrc: 'data',
                    error: function(response) {
                        console.log(response)
                    }
                },
                columns: [{
                        data: 'id',
                        title: 'ID',
                        visible: false
                    },
                    {
                        data: 'name',
                        title: 'Team Name',
                        render: function(data, type, row) {
                            const imageUrl = row.logo ? row.logo : '/path/to/default/image.jpg';
                            return `<div class="d-flex align-items-center file-name-icon">
												<a href="${row.viewUrl}" class="avatar avatar-md avatar-rounded" style="background: #edefff; border-radius: 5px;">
													<img src="${imageUrl}" class="img-fluid" alt="img">
												</a>
												<div class="ms-2">
													<h6 class="fw-medium">
                                                        <a href="${row.viewUrl}">${data}</a>
                                                    </h6>
												</div>
											</div>`;
                        }
                    },
                    {
                        data: 'coach_name',
                        title: 'Coach'
                    },
                    {
                        data: 'manager_name',
                        title: 'Manager'
                    },
                    {
                        data: 'players_count',
                        title: 'Total Players'
                    },
                    {
                        data: null,
                        title: 'Actions', // this must be present
                        orderable: false,
                        searchable: false,
                        width: "170px",
                        className: "text-center",
                        render: function(data, type, row) {
                            return `
                                <a class="btn btn-icon btn-sm btn-success rounded-pill" href="${row.viewUrl}?redirect=${redirectTo}"><i class="fa fa-eye"></i></a>
                                <button class="btn btn-icon btn-sm btn-danger rounded-pill btn-delete" data-id="${row.id}"><i class="fa fa-trash"></i></button>
                                <button class="btn btn-icon btn-sm btn-warning rounded-pill btn-assign-players" data-id="${row.id}" data-bs-toggle="tooltip" data-bs-title="Assign Players">
                                    <span><i class="fa fa-users"></i></span>
                                </button>
                            `;
                        }
                    }
                ]
            });

            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                new bootstrap.Tooltip(el);
            });

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
                            url: '{{ route('admin.teams.destroy', ':selectedId') }}'
                                .replace(':selectedId', selectedId),
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire(
                                    'Deleted!',
                                    'The team has been deleted.',
                                    'success'
                                );
                                $('#tbl-players').DataTable().ajax.reload();
                            },
                            error: function(response) {
                                console.log(response)
                                Swal.fire(
                                    'Error!',
                                    'There was a problem deleting the team.',
                                    'error'
                                );
                            }
                        });
                    }
                });

            });

            $(document).on('click', '.btn-assign-players', function() {
                const teamId = $(this).data('id');
                $('#modal_team_id').val(teamId);
                $('#player_selector').val(null).trigger('change');
                $('#assignPlayerModal').modal('show');
            });

            $('#assignPlayerForm').on('submit', function(e) {
                e.preventDefault();

                let form = $(this);
                let formData = form.serialize();

                $.ajax({
                    url: "{{ route('admin.teams.assign-players') }}",
                    method: "POST",
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    beforeSend: function() {
                        $('.assign-btn').prop('disabled', true).text('Assigning...');
                    },
                    success: function(response) {
                        $('.assign-btn').prop('disabled', false).text('Assign Players');

                        if (response.success) {
                            $('#assignPlayerModal').modal('hide');

                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message ??
                                    'Players assigned successfully!',
                            });

                            if (typeof teamTable !== 'undefined') {
                                teamTable.ajax.reload();
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message ?? 'Something went wrong',
                            });
                        }
                    },
                    error: function(xhr) {
                        $('.assign-btn').prop('disabled', false).text('Assign Players');

                        let errors = xhr.responseJSON?.errors;
                        console.log(errors)
                        if (errors) {
                            let msg = Object.values(errors).flat().join("\n");

                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: msg,
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Request failed. Please try again.',
                            });
                        }
                    }
                });
            });
        });
    </script>
@endpush
