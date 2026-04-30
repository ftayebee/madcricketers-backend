@extends('admin.layouts.theme')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card custom-card-border">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">System Role List</h5>
                    <div>
                        @if(Auth::user()->can('roles-create'))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalCenter">
                            Add New
                        </button>
                        <form action="{{ route('admin.settings.roles.seed') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                            Seed DB
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12 table-responsive">
                            <table class="table datatable no-footer" id="tbl-roleManagement">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Role Name</th>
                                        <th>Granted Permissions</th>
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

    <div class="modal fade" id="exampleModalCenter" tabindex="-1" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Add New Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('admin.settings.roles.store')}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" class="form-control" id="">
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

    <div class="modal fade" id="editRoleModal" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Update Role</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form action="" id="form-role-update">
                    @csrf
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Role Name</label>
                                    <input type="text" class="form-control" name="name" id="roleName">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add_role" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Role</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.settings.roles.store') }}" id="form-role-store">
                    @csrf
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Role Name</label>
                                    <input type="text" class="form-control" name="name">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-control" name="status">
                                        <option value="">Select</option>
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function(){

            $('#tbl-roleManagement').DataTable({
                processing: false,
                serverSide: false,
                searching: true,
                responsive: false,
                autoWidth: false,
                destroy: true,
                ajax: {
                    url: '/admin/settings/roles/loader',
                    type: 'GET',
                    dataSrc: 'data',
                },
                columns: [
                    { data: 'id', title: 'ID' },
                    { data: 'name', title: 'Role Name' },
                    { data: 'grantedPermissions', title: 'Granted Permissions', className: 'text-center' },
                    {
                        data: null,
                        title: 'Actions', // this must be present
                        orderable: false,
                        searchable: false,
                        width: "120px",
                        className: "text-center",
                        render: function (data, type, row) {
                            return `
                                <a class="btn btn-icon btn-sm btn-soft-success rounded-pill" href="${row.viewUrl}">
                                    <i class="fa fa-lock"></i>
                                </a>
                                <button class="btn btn-icon btn-sm btn-soft-warning rounded-pill btn-editModal"
                                    data-id="${row.id}" data-name="${row.name}" data-status="${row.status}">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button class="btn btn-icon btn-sm btn-soft-danger rounded-pill btn-deleteRole"
                                    data-id="${row.id}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            `;

                        }
                    }
                ]
            });

            $(document).on('click', '.btn-editModal', function () {
                // Get the role data from the clicked button's data attributes
                const roleId = $(this).data('id');
                const roleName = $(this).data('name');
                const roleStatus = $(this).data('status');
                console.log(roleName , roleStatus, roleId);

                // Change the form action to the correct URL
                $('#editRoleModal #editRoleForm').attr('action', '{{ route("admin.settings.roles.update", ':roleId') }}'.replace(':roleId', roleId));

                // Open the modal
                $('#editRoleModal').on('shown.bs.modal', function () {
                    $('#editRoleModal #roleName').val(roleName);
                    $('#editRoleModal #roleStatus').val(roleStatus);
                });
                $('#editRoleModal').modal('show');
            });

            $(document).on('click', '.btn-deleteRole', function () {
                const roleId = $(this).data('id');

                // Trigger SweetAlert confirmation
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
                            url: '{{ route("admin.settings.roles.destroy", ':roleId') }}'.replace(':roleId', roleId),
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': "{{csrf_token()}}"
                            },
                            success: function (response) {
                                Swal.fire(
                                    'Deleted!',
                                    'The role has been deleted.',
                                    'success'
                                );
                                // Optionally, reload the DataTable or perform other actions after deletion
                                $('#tbl-roleManagement').DataTable().ajax.reload();
                            },
                            error: function (response) {
                                console.log(response)
                                Swal.fire(
                                    'Error!',
                                    'There was a problem deleting the role.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            $('#form-role-store').on('submit', function(event) {
                event.preventDefault();

                let form = $(this);
                let url = form.attr('action');
                let formData = form.serialize();

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if(response.success){
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Role successfully added!',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            $('#form-role-store')[0].reset();
                            $('#yourModalId').modal('hide');
                            $('#tbl-roleManagement').DataTable().ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong. Please try again.'
                        });
                    }
                });
            });

        });
    </script>
@endpush
