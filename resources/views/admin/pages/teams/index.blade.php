@extends('admin.layouts.theme')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
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

    <div class="modal fade" id="add-team" tabindex="-1" aria-labelledby="add-teamTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-teamTitle">Add New Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('admin.teams.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" class="form-control" id="">
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="form-group">
                                    <label for="coach_name">Coach Name</label>
                                    <input type="text" name="coach_name" class="form-control" id="">
                                </div>
                            </div>
                            <div class="col-12 mb-3">
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
@endsection

@push('scripts')
    <script>
        $(document).ready(function(){
            const redirectTo = "{{url()->current()}}"
            $('#tbl-players').DataTable({
                processing: false,
                serverSide: false,
                searching: true,
                responsive: false,
                autoWidth: false,
                destroy: true,
                ajax: {
                    url: '{{ route("admin.teams.loader") }}',
                    type: 'GET',
                    dataSrc: 'data',
                    error: function(response) {
                        console.log(response)
                    }
                },
                columns: [
                    { data: 'id', title: 'ID', visible: false },
                    {
                        data: 'name',
                        title: 'Team Name',
                        render: function (data, type, row) {
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
                    { data: 'coach_name', title: 'Coach' },
                    { data: 'manager_name', title: 'Manager' },
                    { data: 'players_count', title: 'Total Players' },
                    {
                        data: null,
                        title: 'Actions', // this must be present
                        orderable: false,
                        searchable: false,
                        width: "120px",
                        className: "text-center",
                        render: function (data, type, row) {
                            return `
                                <a class="btn btn-icon btn-sm btn-soft-success rounded-pill" href="${row.viewUrl}?redirect=${redirectTo}"><i class="fa fa-eye"></i></a>
                                <button class="btn btn-icon btn-sm btn-soft-danger rounded-pill btn-delete" data-id="${row.id}"><i class="fa fa-trash"></i></button>
                                <button class="btn btn-icon btn-sm btn-soft-warning rounded-pill btn-assign-players" data-id="${row.id}" data-bs-toggle="tooltip" data-bs-title="Assign Players">
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
                            url: '{{ route("admin.settings.users.destroy", ':selectedId') }}'.replace(':selectedId', selectedId),
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': "{{csrf_token()}}"
                            },
                            success: function (response) {
                                Swal.fire(
                                    'Deleted!',
                                    'The user has been deleted.',
                                    'success'
                                );
                                $('#tbl-players').DataTable().ajax.reload();
                            },
                            error: function (response) {
                                console.log(response)
                                Swal.fire(
                                    'Error!',
                                    'There was a problem deleting the user.',
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
