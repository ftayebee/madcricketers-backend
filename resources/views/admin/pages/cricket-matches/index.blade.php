@extends('admin.layouts.theme')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Daily Cricket Matches List</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-tournament">
                        Add New
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12 table-responsive">
                            <table id="tbl-cricket-matches" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Match Title</th>
                                        <th>Teams</th>
                                        <th>Tournament</th>
                                        <th>Match Date</th>
                                        <th>Venue</th>
                                        <th>Match Type</th>
                                        <th>Status</th>
                                        <th>Result</th>
                                        <th>Max Overs</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Data will be loaded via AJAX (tableLoader) --}}
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add-tournament" tabindex="-1" aria-labelledby="add-tournamentTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- wider modal -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-tournamentTitle">Add New Tournament</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('admin.tournaments.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Tournament Name</label>
                                <input type="text" name="name" class="form-control"
                                    placeholder="Enter tournament name" required>
                            </div>

                            <div class="col-md-6">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" name="location" class="form-control"
                                    placeholder="Enter venue or city">
                            </div>

                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="upcoming">Upcoming</option>
                                    <option value="ongoing">Ongoing</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="format" class="form-label">Format</label>
                                <select name="format" class="form-select" id="format">
                                    <option value=""></option>
                                    <option value="group">Group</option>
                                    <option value="round-robin">Round Robin</option>
                                    <option value="knockout">Knockout</option>
                                </select>
                            </div>

                            <div class="col-md-6 format-dependent" id="group-fields" style="display: none;">
                                <label for="number_of_groups" class="form-label">Number of Groups</label>
                                <input type="number" name="group_count" class="form-control" min="2" step="2">
                            </div>

                            <div class="col-md-6">
                                <label for="trophy_image" class="form-label">Trophy Image</label>
                                <input type="file" name="trophy_image" class="form-control" accept="image/*">
                            </div>

                            <div class="col-md-6">
                                <label for="logo" class="form-label">Tournament Logo</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" rows="3" class="form-control" placeholder="Write tournament description..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Tournament</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="assign-tournament-teams" tabindex="-1" aria-labelledby="add-tournamentTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-tournamentTitle">Add Teams To Tournament</h5>
                    <p class="modal-title" id="add-tournamentSubTitle"></p>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('admin.tournaments.assign-teams') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="status" class="form-label">Team List</label>
                                <select name="team_id[]" class="form-select form-control" multiple>
                                    @foreach ($validTeams as $teamInfo)
                                        <option value="{{ $teamInfo->id }}">{{ $teamInfo->name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="tournament_id" value="">

                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input checkbox-md" type="checkbox" role="switch"
                                        id="flexSwitchCheckChecked" checked="" name="seperate_teams">
                                    <label class="form-check-label" for="flexSwitchCheckChecked">Seperate Teams to
                                        Groups</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Tournament</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const redirectTo = "{{ url()->current() }}"
            $('#tbl-cricket-matches').DataTable({
                processing: false,
                serverSide: false,
                searching: true,
                responsive: true,
                autoWidth: false,
                destroy: true,
                ajax: {
                    url: '{{ route('admin.cricket-matches.loader') }}', // 👈 your route
                    type: 'GET',
                    dataSrc: 'data',
                    error: function(response) {
                        console.log('Error loading cricket matches:', response);
                    }
                },
                columns: [{
                        data: 'id',
                        title: 'ID',
                        visible: false
                    },

                    {
                        data: 'title',
                        title: 'Match',
                        render: function(data, type, row) {
                            return `
                                <div class="d-flex align-items-center">
                                    <div class="ms-2">
                                        <h6 class="fw-medium mb-0">
                                            <a href="${row.viewUrl}">${data}</a>
                                        </h6>
                                        <small class="text-muted">
                                            ${row.team_a ?? ''} vs ${row.team_b ?? ''}
                                        </small>
                                    </div>
                                </div>`;
                        }
                    },

                    {
                        data: 'match_date',
                        title: 'Date',
                        className: 'text-center'
                    },

                    {
                        data: 'venue',
                        title: 'Venue'
                    },

                    {
                        data: 'tournament',
                        title: 'Tournament',
                        render: function(data) {
                            return data ?? '<span class="text-muted">-</span>';
                        }
                    },

                    {
                        data: 'match_type',
                        title: 'Type',
                        className: 'text-center'
                    },

                    {
                        data: 'status',
                        title: 'Status',
                        className: 'text-center',
                        render: function(data) {
                            const badgeClass = {
                                Upcoming: 'bg-primary',
                                Live: 'bg-warning',
                                Completed: 'bg-success'
                            } [data] || 'bg-secondary';

                            return `<span class="badge ${badgeClass}">${data}</span>`;
                        }
                    },

                    {
                        data: 'max_overs',
                        title: 'Overs',
                        className: 'text-center'
                    },

                    {
                        data: 'winning_team',
                        title: 'Winner',
                        render: function(data) {
                            return data ?? '<span class="text-muted">-</span>';
                        }
                    },

                    {
                        data: 'result_summary',
                        title: 'Result',
                        render: function(data) {
                            return data ?? '<span class="text-muted">Pending</span>';
                        }
                    },

                    {
                        data: 'actions',
                        title: 'Actions',
                        orderable: false,
                        searchable: false,
                        className: "text-center",
                        render: function(data, type, row) {
                            return `
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-soft-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        ${row.canView ? `
                                                <li>
                                                    <a class="dropdown-item" href="${row.viewUrl}">
                                                        <i class="fa fa-eye me-2"></i> View
                                                    </a>
                                                </li>` : ''}
                                        ${row.canScore ? `
                                            <li>
                                                <a class="dropdown-item" href="${row.startUrl}">
                                                    <i class="fa fa-edit me-2"></i> Edit Scoreboard
                                                </a>
                                            </li>` : ''}
                                        ${row.canEdit ? `
                                                <li>
                                                    <a class="dropdown-item" href="${row.editUrl}">
                                                        <i class="fa fa-edit me-2"></i> Edit Details
                                                    </a>
                                                </li>` : ''}

                                        ${row.canDelete ? `
                                                <li>
                                                    <button class="dropdown-item btn-delete" data-id="${row.id}">
                                                        <i class="fa fa-trash me-2"></i> Delete
                                                    </button>
                                                </li>` : ''}
                                    </ul>
                                </div>
                            `;
                        }
                    }

                ]
            });

            $('select[name="team_id[]"]').select2({
                dropdownParent: $('#assign-tournament-teams'),
                width: "100%",
                closeOnSelect: false
            });

            $(document).on('click', '.btn-assign-players', function() {
                const tournamentId = $(this).data('id');
                $('input[name="tournament_id"]').val(tournamentId);
                $('#assign-tournament-teams').modal('show');
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
                // else if (value === "round-robin") {
                //     document.getElementById("round-robin-fields").style.display = "block";
                // } else if (value === "knockout") {
                //     document.getElementById("knockout-fields").style.display = "block";
                // }
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
                            url: '{{ route('admin.tournaments.destroy', ':selectedId') }}'
                                .replace(':selectedId', selectedId),
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire(
                                    'Deleted!',
                                    'The Tournament has been deleted.',
                                    'success'
                                );
                                $('#tbl-players').DataTable().ajax.reload();
                            },
                            error: function(response) {
                                console.log(response)
                                Swal.fire(
                                    'Error!',
                                    'There was a problem deleting the tournament.',
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
