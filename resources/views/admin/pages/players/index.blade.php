@extends('admin.layouts.theme')

@section('content')
    @push('styles')
        <style>
            .choices[data-type*="select-one"]:after {
                font-family: 'FontAwesome' !important;
                content: "\f107" !important;
                border: none !important;
                height: auto !important;
                width: auto !important;
                right: 11.5px !important;
            }

            .choices[data-type*="select-one"].is-disabled:after {
                content: "\f107" !important;
            }

            .status-wrapper .btn-check {
                margin-left: 5px;
            }
        </style>
    @endpush
    <meta name="csrf-token" content="{{csrf_token()}}" />
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-end">
                                <div class="w-25">
                                    <select name="filter_type" id="filter_type" class="form-control w-50" data-choices
                                        data-choices-search-false>
                                        <option value="">Select Filter</option>
                                        <option value="all">All</option>
                                        <option value="guest">Guest Player</option>
                                        <option value="registered">Registered Player</option>
                                    </select>
                                </div>
                                <div class="w-25" style="margin-left: 20px;">
                                    <select name="filter_role" id="filter_role" class="form-control w-50" data-choices
                                        data-choices-search-false>
                                        <option value="">Select Filter</option>
                                        <option value="all">All</option>
                                        <option value="all rounder">All Rounder</option>
                                        <option value="batsman">Batsman</option>
                                        <option value="bowler">Bowler</option>
                                        <option value="wicketkeeper">Wicketkeeper</option>
                                    </select>
                                </div>
                                <a href="{{ route('admin.settings.users.create') }}" class="btn btn-primary"
                                    style="margin-left: 20px;">
                                    <i class="fa fa-plus"></i> Add New
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12 table-responsive">
                            <table class="table datatable no-footer" id="tbl-players">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Type</th>
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
        $(document).ready(function() {
            const redirectTo = "{{ url()->current() }}"
            const playersTable = $('#tbl-players').DataTable({
                processing: false,
                serverSide: false,
                searching: true,
                responsive: false,
                autoWidth: false,
                destroy: true,
                ajax: {
                    url: '{{ route('admin.players.loader') }}',
                    type: 'GET',
                    dataSrc: 'data',
                },
                columns: [{
                        data: 'id',
                        title: 'ID'
                    },
                    {
                        data: 'name',
                        title: 'Full Name',
                        searchable: true,
                        orderable: true,
                        render: function(data, type, row) {
                            const imageUrl = row.image ? row.image : '/path/to/default/image.jpg';
                            return `<div class="d-flex align-items-center file-name-icon">
                                        <a href="${row.viewUrl}" class="avatar avatar-lg avatar-rounded" style="background: #edefff; border-radius: 5px;">
                                            <img src="${imageUrl}" class="img-fluid  rounded-3 border border-3" alt="img" style="border-color: #c0ffab!important;">
                                        </a>
                                        <div class="ms-2">
                                            <h6 class="fw-medium fs-14 m-0">
                                                ${data}
                                            </h6>
                                        </div>
                                    </div>`;
                        }
                    },
                    {
                        data: 'email',
                        title: 'Email',
                        className: "fs-14",
                    },
                    {
                        data: 'phone',
                        title: 'Phone',
                        className: "fs-14",
                    },
                    {
                        data: 'playerRole',
                        title: 'Role',
                        className: "fs-14",
                        render: function(data, type, row) {
                            if (!data) return '';

                            const formatted = data
                                .replace(/-/g, ' ')
                                .replace(/\w\S*/g, function(txt) {
                                    return txt.charAt(0).toUpperCase() + txt.substr(1)
                                        .toLowerCase();
                                });

                            return formatted;
                        }
                    },
                    {
                        data: 'playerType',
                        title: 'Type',
                        width: "220px",
                        render: function(data, type, row) {
                            const options = ['guest', 'registered'];
                            let selectOptions = options.map(opt => {
                                return `<option value="${opt}" ${opt === data ? 'selected' : ''}>${opt.charAt(0).toUpperCase() + opt.slice(1)}</option>`;
                            }).join('');

                            // Determine border color
                            let borderClass = '';
                            if (data === 'guest') borderClass = 'border-warning';
                            else if (data === 'registered') borderClass = 'border-success';

                            return `
                                <div class="status-wrapper">
                                    <select class="form-select form-select-sm status-select fs-14 ${borderClass}" name="player_type-${row.id}" data-id="${row.id}">
                                        ${selectOptions}
                                    </select>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'status',
                        title: 'Status',
                        className: "fs-14",
                        render: function(data, type, row) {
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
                        className: "text-center fs-14",
                        render: function(data, type, row) {
                            return `
                                <a class="btn btn-icon btn-sm btn-soft-success rounded-pill" href="${row.viewUrl}?redirect=${redirectTo}"><i class="fa fa-eye"></i></a>
                                <button class="btn btn-icon btn-sm btn-soft-danger rounded-pill btn-delete" data-id="${row.id}"><i class="fa fa-trash"></i></button>
                            `;
                        }
                    }
                ]
            });

            $('#filter_type').on('change', function() {
                const filterType = $(this).val();
                if (filterType != 'all') {
                    playersTable.column(5).search(filterType).draw();
                } else {
                    playersTable.column(5).search('').draw();
                }
            });

            $('#filter_role').on('change', function() {
                const filterRole = $(this).val();
                if (filterRole != 'all') {
                    playersTable.column(4).search(filterRole).draw();
                } else {
                    playersTable.column(4).search('').draw();
                }
            });

            $('#tbl-players').on('change', '.status-select', function() {
                const $select = $(this);
                const rowId = $select.data('id');
                const newStatus = $select.val();
                
                $select.removeClass('border-warning border-success');
                if(newStatus === 'guest') $select.addClass('border-warning');
                else if(newStatus === 'registered') $select.addClass('border-success');

                // Call your update logic (AJAX)
                fetch(`/admin/players/approve/${rowId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            approve: newStatus,
                            redirection: redirectTo
                        })
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if(data.success){
                            console.log('Status updated:', data);
                            window.location.href = data.redirect;
                        }
                    })
                    .catch(error => {
                        console.error('Update failed:', error);
                        alert('Failed to update player status.');
                    });
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
                            url: '{{ route('admin.settings.users.destroy', ':selectedId') }}'
                                .replace(':selectedId', selectedId),
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire(
                                    'Deleted!',
                                    'The user has been deleted.',
                                    'success'
                                );
                                $('#tbl-players').DataTable().ajax.reload();
                            },
                            error: function(response) {
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
