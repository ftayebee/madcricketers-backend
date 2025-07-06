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
                                        @foreach ($actions as $item)
                                        <th scope="col" class="text-center">{{strtoupper($item)}}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $permissionSet = collect($role->permissions)->map(function($p){
                                            return $p->module . ':' . $p->action;
                                        })->toArray();
                                    @endphp
                                    @foreach ($modulesList as $item)
                                        <tr>
                                            <td>
                                                {{strtoupper($item)}}
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-check-md m-auto d-flex justify-content-center">
                                                    <input type="checkbox"
                                                        class="form-check-input permission-checkbox"
                                                        data-module="{{ $item }}"
                                                        data-action="all"
                                                        data-role="{{ $role->id ?? '' }}">
                                                </div>
                                            </td>
                                            @foreach ($actions as $action)
                                                @php
                                                    $key = $item . ':' . $action;
                                                    $isChecked = in_array($key, $permissionSet);
                                                @endphp
                                                <td class="text-center">
                                                    <div class="form-check form-check-md text-center m-auto d-flex justify-content-center" >
                                                        <input type="checkbox"
                                                            class="form-check-input permission-checkbox"
                                                            data-module="{{ $item }}"
                                                            data-action="{{ $action }}"
                                                            data-role="{{ $role->id ?? '' }}"
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
        $(document).ready(function(){

            if($('#btn-generate-permissions')){
                $('#btn-generate-permissions').on('click', function () {
                    $.ajax({
                        url: "{{ route('admin.permissions.generate') }}",
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Permissions Generated',
                                text: response.message || 'Permissions have been successfully generated.',
                                confirmButtonText: 'OK'
                            });
                        },
                        error: function (xhr) {
                            const message = xhr.responseJSON?.message || 'Something went wrong.';

                            // SweetAlert error
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: message
                            });
                        }
                    });
                });
            }

            if($('.permission-checkbox')){
                $(document).on('change', '.permission-checkbox', function () {
                    let checkbox = $(this);
                    let module = checkbox.data('module');
                    let action = checkbox.data('action');
                    let roleId = checkbox.data('role');
                    let checked = checkbox.is(':checked');

                    $.ajax({
                        url: "{{ route('admin.permissions.update') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            module: module,
                            action: action,
                            role_id: roleId,
                            checked: checked
                        },
                        success: function (response) {
                            if(response.success){
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end', // top-right corner
                                    icon: 'success',
                                    title: response.message || 'Permission updated successfully',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true
                                }).then(() => {
                                    window.location.reload(); // Correctly calling the reload method
                                });
                            } else {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end', // top-right corner
                                    icon: 'warning',
                                    title: response.message || 'Permission not updated successfully',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true
                                }).then(() => {
                                    window.location.reload(); // Correctly calling the reload method
                                });
                            }
                        },
                        error: function (xhr) {
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
        });
    </script>
@endpush
