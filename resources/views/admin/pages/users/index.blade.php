@extends('admin.layouts.theme')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Users List</h5>
                    <a href="{{route('admin.settings.users.create')}}" class="btn btn-primary">
                        Add New
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12 table-responsive">
                            <table class="table datatable no-footer" id="tbl-users">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>National ID</th>
                                        <th>Role</th>
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
@endsection

@push('scripts')
    <script>
        $(document).ready(function(){
            const redirectTo = "{{url()->current()}}"
            $('#tbl-users').DataTable({
                processing: false,
                serverSide: false,
                searching: true,
                responsive: false,
                autoWidth: false,
                destroy: true,
                ajax: {
                    url: '{{ route("admin.settings.users.loader") }}',
                    type: 'GET',
                    dataSrc: 'data',
                    // success: function(response){
                    //     console.dir(response)
                    // },
                    // error: function(response){
                    //     console.dir(response)
                    // },
                },
                columns: [
                    { data: 'id', title: 'ID' },
                    {
                        data: 'name',
                        title: 'Full Name',
                        render: function (data, type, row) {
                            const imageUrl = row.image ? row.image : '/path/to/default/image.jpg';
                            return `<div class="d-flex align-items-center file-name-icon">
												<a href="${row.viewUrl}" class="avatar avatar-md avatar-rounded" style="background: #FAD59A;">
													<img src="${imageUrl}" class="img-fluid" alt="img" style="height: 55px!important; width: 100%!important;">
												</a>
												<div class="ms-2">
													<h6 class="fw-medium">
                                                        <a href="${row.viewUrl}">${data}</a>
                                                    </h6>
												</div>
											</div>`;
                        }
                    },
                    { data: 'email', title: 'Email' },
                    { data: 'phone', title: 'Phone' },
                    { data: 'national_id', title: 'National ID' },
                    {
                        data: 'role',
                        title: 'Role',
                        render: function (data, type, row) {
                            let badgeClass = '';
                            switch (row.roleSlug) {
                                case 'super-admin':
                                    badgeClass = 'bg-soft-info';
                                    break;
                                case 'admin':
                                    badgeClass = 'bg-soft-primary';
                                    break;
                                case 'employee':
                                    badgeClass = 'bg-soft-warning';
                                    break;
                                case 'maintenance-staff':
                                    badgeClass = 'bg-soft-secondary';
                                    break;
                                default:
                                    badgeClass = 'bg-soft-dark';
                            }
                            return `<span class="badge ${badgeClass} text-uppercase">${data}</span>`;
                        }
                    },
                    {
                        data: 'status',
                        title: 'Status',
                        render: function (data, type, row) {
                            return data == 'active' ?
                                `<span class="badge bg-success text-uppercase">${data}</span>` :
                                `<span class="badge bg-danger text-uppercase">${data}</span>`;
                        }
                    },
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
                                <button class="btn btn-icon btn-sm btn-soft-danger rounded-pill btn-deleteRole" data-id="${row.id}"><i class="fa fa-trash"></i></button>
                            `;
                        }
                    }
                ]
            });

            $(document).on('click', '.btn-deleteRole', function () {
                const roleId = $(this).data('id');

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
                        // Make the delete request (assuming a DELETE method)
                        $.ajax({
                            url: '{{ route("admin.settings.users.destroy", ':roleId') }}'.replace(':roleId', roleId),
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
                                $('#tbl-roleManagement').DataTable().ajax.reload();
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
