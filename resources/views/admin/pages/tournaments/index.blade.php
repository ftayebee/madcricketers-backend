@extends('admin.layouts.theme')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Teams List</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-tournament">
                        Add New
                    </button>
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
                                <input type="text" name="name" class="form-control" placeholder="Enter tournament name" required>
                            </div>

                            <div class="col-md-6">
                                <label for="slug" class="form-label">Slug (URL friendly)</label>
                                <input type="text" name="slug" class="form-control" placeholder="Optional - Auto Generated if Empty">
                            </div>

                            <div class="col-md-6">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" name="location" class="form-control" placeholder="Enter venue or city">
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
                                <div class="mb-3">
                                    <label for="format" class="form-label">Format</label>
                                    <select name="format" class="form-select" id="format">
                                        <option value=""></option>
                                        <option value="group">Group</option>
                                        <option value="round-robin">Round Robin</option>
                                        <option value="knockout">Knockout</option>
                                    </select>
                                </div>

                                {{-- Group Specific --}}
                                <div class="mb-3 format-dependent" id="group-fields" style="display: none;">
                                    <label for="number_of_groups">Number of Groups</label>
                                    <input type="number" name="number_of_groups" class="form-control" min="2" step="2">
                                </div>
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
                            const imageUrl = row.logo ? row.logo : '/path/to/default/image.jpg';
                            return `
                                <div class="d-flex align-items-center">
                                    <a href="${row.viewUrl}" class="avatar avatar-md avatar-rounded" style="background: #edefff; border-radius: 5px;">
                                        <img src="${imageUrl}" class="img-fluid" alt="img">
                                    </a>
                                    <div class="ms-2">
                                        <h6 class="fw-medium mb-0">
                                            <a href="${row.viewUrl}">${data}</a>
                                        </h6>
                                    </div>
                                </div>`;
                        }
                    },
                    { data: 'location', title: 'Location' },
                    { data: 'start_date', title: 'Start Date' },
                    { data: 'end_date', title: 'End Date' },
                    {
                        data: 'players_count',
                        title: 'Playing Teams',
                        render: function(data) {
                            return data ?? 0;
                        }
                    },
                    {
                        data: 'status',
                        title: 'Status',
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
                        data: null,
                        title: 'Actions',
                        orderable: false,
                        searchable: false,
                        className: "text-center",
                        render: function (data, type, row) {
                            return `
                                <a class="btn btn-icon btn-sm btn-soft-success rounded-pill" href="${row.viewUrl}">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <button class="btn btn-icon btn-sm btn-soft-danger rounded-pill btn-delete" data-id="${row.id}">
                                    <i class="fa fa-trash"></i>
                                </button>
                                <button class="btn btn-icon btn-sm btn-soft-warning rounded-pill btn-assign-players" data-id="${row.id}" data-bs-toggle="tooltip" data-bs-title="Assign Teams">
                                    <i class="fa fa-users"></i>
                                </button>
                            `;
                        }
                    }
                ]
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
