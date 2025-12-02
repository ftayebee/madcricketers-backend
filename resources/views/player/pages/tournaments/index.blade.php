@extends('player.layouts.theme')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card custom-card-border">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Teams List</h5>
                    <a href="{{route('admin.tournaments.create')}}" class="btn btn-primary" >
                        Add New
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12 table-responsive">
                            <table class="table datatable no-footer" id="tbl-tournaments">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Location</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Playing Teams</th>
                                        <th>Status</th>
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

    <div class="modal fade" id="assign-tournament-teams" tabindex="-1" aria-labelledby="add-tournamentTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-tournamentTitle">Add Teams To Tournament</h5>
                    <p class="modal-title" id="add-tournamentSubTitle"></p>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('admin.tournaments.assign-teams') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="status" class="form-label">Team List</label>
                                <select name="team_id[]" class="form-select form-control" multiple>
                                    @foreach ($validTeams as $teamInfo)
                                    <option value="{{$teamInfo->id}}">{{$teamInfo->name}}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="tournament_id" value="">

                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input checkbox-md" type="checkbox" role="switch" id="flexSwitchCheckChecked" checked="" name="seperate_teams">
                                    <label class="form-check-label" for="flexSwitchCheckChecked">Seperate Teams to Groups</label>
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
        $(document).ready(function(){
            const redirectTo = "{{url()->current()}}"
            $('#tbl-tournaments').DataTable({
                processing: false,
                serverSide: false,
                searching: true,
                responsive: true,
                autoWidth: false,
                destroy: true,
                ajax: {
                    url: '{{ route("admin.tournaments.loader") }}',
                    type: 'GET',
                    dataSrc: 'data',
                    error: function(response) {
                        console.log('Error loading data:', response);
                    }
                },
                columns: [
                    { data: 'id', title: 'ID', visible: false },
                    {
                        data: 'name',
                        title: 'Name',
                        render: function (data, type, row) {
                            const imageUrl = row.logo ? row.logo : null;
                            return `
                                <div class="d-flex align-items-center">
                                    ${imageUrl ? `
                                    <a href="${row.viewUrl}" class="avatar avatar-md avatar-rounded" style="background: #edefff; border-radius: 5px;">
                                        <img src="${imageUrl}" class="img-fluid" alt="img">
                                    </a>` : ''}
                                    <div class="ms-2">
                                        <h5 class="fw-medium mb-0">
                                            <a href="${row.viewUrl}">${data}</a>
                                        </h5>
                                    </div>
                                </div>`;
                        }
                    },
                    { data: 'location', title: 'Location' },
                    { data: 'start_date', title: 'Start Date', className: 'text-center', },
                    { data: 'end_date', title: 'End Date',className: 'text-center', },
                    {
                        data: 'playing_teams',
                        title: 'Playing Teams',
                        className: 'text-center',
                        render: function(data) {
                            return data ?? 0;
                        }
                    },
                    {
                        data: 'status',
                        title: 'Status',
                        className: 'text-center',
                        render: function (data) {
                            const badgeClass = {
                                upcoming: 'bg-primary',
                                ongoing: 'bg-warning',
                                completed: 'bg-success'
                            }[data] || 'bg-secondary';

                            return `<span class="badge ${badgeClass}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                        }
                    },
                    {
                        data: 'actions',
                        title: 'Actions',
                        orderable: false,
                        searchable: false,
                        className: "text-center",
                        render: function (data, type, row) {
                            return `
                                ${row.canView ? `<a class="btn btn-icon btn-sm btn-soft-success rounded-pill" href="${row.viewUrl}">
                                    <i class="fa fa-eye"></i>
                                </a>` : ''}
                                ${row.canDelete ? `<button class="btn btn-icon btn-sm btn-soft-danger rounded-pill btn-delete" data-id="${row.id}">
                                    <i class="fa fa-trash"></i>
                                </button>` : ''}
                                ${row.canAssignTeam ? `<button class="btn btn-icon btn-sm btn-soft-warning rounded-pill btn-assign-players" data-id="${row.id}">
                                    <span data-bs-toggle="tooltip" data-bs-title="Assign Teams">
                                    <i class="fa fa-users"></i>
                                    </span>
                                </button>` : ''}
                            `;
                        }
                    }
                ],
                // drawCallback: function(settings) {
                //     console.log("IN DRAW CALLBACK")
                //     $('select[name="team_id"]').select2({
                //         dropdownParent: $('#assign-tournament-teams'),
                //         width: "100%"
                //     });
                //     console.log("After DRAW CALLBACK")
                // }
            });

            $('select[name="team_id[]"]').select2({
                dropdownParent: $('#assign-tournament-teams'),
                width: "100%",
                closeOnSelect: false
            });

            $(document).on('click', '.btn-assign-players', function () {
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

            $(document).on('click', '.btn-delete', function () {
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
                            url: '{{ route("admin.tournaments.destroy", ':selectedId') }}'.replace(':selectedId', selectedId),
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': "{{csrf_token()}}"
                            },
                            success: function (response) {
                                Swal.fire(
                                    'Deleted!',
                                    'The Tournament has been deleted.',
                                    'success'
                                );
                                $('#tbl-players').DataTable().ajax.reload();
                            },
                            error: function (response) {
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
