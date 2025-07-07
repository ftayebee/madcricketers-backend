@extends('admin.layouts.theme')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Permission List</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12 table-responsive">
                            <table class="table text-nowrap table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">Module</th>
                                        <th scope="col" class="text-center">All</th>
                                        @foreach ($actions as $action)
                                            <th scope="col" class="text-center">{{ strtoupper($action) }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($moduleList as $moduleName => $modulePermissions)
                                        @php
                                            // Check if all permissions for this module are assigned to role
                                            $allChecked = collect($modulePermissions)->every(function ($permission) use ($rolePermissions) {
                                                return in_array($permission, $rolePermissions);
                                            });
                                        @endphp
                                        <tr>
                                            <td>{{ strtoupper($moduleName) }}</td>
                                            <td class="text-center">
                                                <div class="form-check form-check-md m-auto d-flex justify-content-center">
                                                    <input type="checkbox"
                                                        class="form-check-input permission-checkbox select-all"
                                                        data-module="{{ $moduleName }}" data-action="all"
                                                        data-role="{{ $role->id }}" {{ $allChecked ? 'checked' : '' }}>
                                                </div>
                                            </td>

                                            @foreach ($actions as $action)
                                                @php
                                                    // Build expected permission name for this module + action
                                                    $expectedPermission = $moduleName . '-' . $action;
                                                    $isChecked = in_array($expectedPermission, $rolePermissions);
                                                @endphp
                                                <td class="text-center">
                                                    <div
                                                        class="form-check form-check-md text-center m-auto d-flex justify-content-center">
                                                        <input type="checkbox" class="form-check-input permission-checkbox"
                                                            data-module="{{ $moduleName }}"
                                                            data-action="{{ $action }}"
                                                            data-role="{{ $role->id }}"
                                                            {{ $isChecked ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
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
        $(document).ready(function() {
            if ($('.permission-checkbox')) {
                $(document).on('change', '.permission-checkbox', function() {
                    let checkbox = $(this);
                    let module = checkbox.data('module');
                    let action = checkbox.data('action');
                    let roleId = checkbox.data('role');
                    let checked = checkbox.is(':checked');

                    console.log("Module: " + module)
                    console.log("action: " + action)
                    console.log("roleId: " + roleId)
                    console.log("checked: " + typeof checked)

                    $.ajax({
                        url: "{{ route('admin.settings.permissions.update') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            module: module,
                            action: action,
                            role_id: roleId,
                            checked: checked
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end', // top-right corner
                                    icon: 'success',
                                    title: response.message ||
                                        'Permission updated successfully',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true
                                }).then(() => {
                                    window.location
                                        .reload(); // Correctly calling the reload method
                                });
                            } else {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end', // top-right corner
                                    icon: 'warning',
                                    title: response.message ||
                                        'Permission not updated successfully',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true
                                }).then(() => {
                                    window.location
                                        .reload(); // Correctly calling the reload method
                                });
                            }
                        },
                        error: function(xhr) {
                            console.log(xhr)
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: 'Something went wrong!',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                            checkbox.prop('checked', !checked); // Revert checkbox
                        }
                    });
                });
            }

            document.querySelectorAll('.select-all').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    let module = this.dataset.module;
                    let checkboxes = document.querySelectorAll(
                        `.permission-checkbox[data-module="${module}"]:not([data-action="all"])`
                        );
                    checkboxes.forEach(cb => cb.checked = this.checked);
                });
            });
        });
    </script>
@endpush
