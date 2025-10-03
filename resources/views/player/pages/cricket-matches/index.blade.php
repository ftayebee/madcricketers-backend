@extends('admin.layouts.theme')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Daily Cricket Matches List</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-cricket-match">
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

    <div class="modal fade" id="add-cricket-match" tabindex="-1" aria-labelledby="add-tournamentTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- wider modal -->
            <div class="modal-content">
                <div class="modal-header" style="background: #06923E!important;">
                    <h5 class="modal-title" id="add-tournamentTitle" style="color: #fff; font-size: 20px; font-weight: 800;margin:0px;">Add New Cricket Match</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('admin.cricket-matches.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Match Title (Optional)</label>
                                <input type="text" name="title" class="form-control" placeholder="Enter Title">
                            </div>

                            <div class="col-md-6">
                                <label for="venue" class="form-label">Match Venue</label>
                                <input type="text" name="venue" class="form-control" placeholder="Enter venue or city">
                            </div>
                            <div class="col-md-6">
                                <label for="team_a_id" class="form-label">Team A</label>
                                <select name="team_a_id" class="form-select select2">
                                    <option value=""></option>
                                    @foreach (\App\Models\Team::all() as $item)
                                        <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="team_b_id" class="form-label">Team B</label>
                                <select name="team_b_id" class="form-select select2">
                                    <option value=""></option>
                                    @foreach (\App\Models\Team::all() as $item)
                                        <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="match_date" class="form-label">Match Date</label>
                                <input type="date" name="match_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="max_overs" class="form-label">Total Overs (Per Innings)</label>
                                <input type="number" name="max_overs" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label for="match_type" class="form-label">Match Type</label>
                                <select name="match_type" class="form-select select2">
                                    <option value="tournament">Tournament</option>
                                    <option value="regular">Regular</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" class="form-select select2">
                                    <option value="live">Live</option>
                                    <option value="upcoming">Upcoming</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Match</button>
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
                                        ${row.canScore && row.status != 'Completed' ? `
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
