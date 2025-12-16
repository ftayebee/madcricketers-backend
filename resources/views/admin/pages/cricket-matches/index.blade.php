@extends('admin.layouts.theme')

@section('content')
    @push('styles')
        <style>
            .choices {
                margin-bottom: 0px !important;
            }

            #tbl-cricket-matches {
                min-height: 520px;
            }

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

            .select2-container .select2-selection--single {
                height: 39px !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 38px !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 39px !important;
            }

            .form-control {
                border-color: #aaa;
            }

            .match-card {
                border-radius: 12px;
                border: 1px solid rgba(0, 0, 0, 0.08);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
                transition: all 0.25s ease-in-out;
            }

            /* Hover Elevation */
            .match-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
            }

            /* Header Styling */
            .match-card .card-header {
                background: linear-gradient(135deg, #f8f9fa, #ffffff);
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
                border-top-left-radius: 12px;
                border-top-right-radius: 12px;
            }

            /* Footer Styling */
            .match-card .card-footer {
                background: #fafafa;
                border-top: 1px solid rgba(0, 0, 0, 0.05);
            }

            /* Mobile-friendly (no hover jump) */
            @media (max-width: 576px) {
                .match-card:hover {
                    transform: none;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
                }
            }
        </style>
    @endpush
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">

                    <div class="row g-2 align-items-center">

                        <!-- Title -->
                        <div class="col-12 col-md-4">
                            <h5 class="mb-0 text-center text-md-start">
                                Daily Cricket Matches
                            </h5>
                        </div>

                        <!-- Filter -->
                        <div class="col-12 col-md-4">
                            <select name="filter_format" id="filter_format" class="form-select w-100" data-choices
                                data-choices-search-false>
                                <option value="">Match Format</option>
                                <option value="all">All</option>
                                <option value="tournament">Tournament</option>
                                <option value="regular">Regular</option>
                            </select>
                        </div>

                        <!-- Actions -->
                        <div class="col-12 col-md-4">
                            <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end">

                                <button type="button" class="btn btn-primary w-100 w-md-auto" data-bs-toggle="modal"
                                    data-bs-target="#add-cricket-match">
                                    <i class="fa fa-plus me-1"></i> Match
                                </button>

                                <button type="button" class="btn btn-outline-primary w-100 w-md-auto"
                                    data-bs-toggle="modal" data-bs-target="#add-team">
                                    <i class="fa fa-users me-1"></i> Team
                                </button>

                            </div>
                        </div>

                    </div>

                </div>

                <div class="card-body">
                    <div class="row" id="matchesCardContainer">
                        <!-- Cards injected via AJAX -->
                    </div>
                </div>
            </div>
        </div>

    </div>

    @include('admin.partials._create-team-modal')

    <div class="modal fade" id="add-cricket-match" tabindex="-1" aria-labelledby="add-tournamentTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- wider modal -->
            <div class="modal-content">
                <div class="modal-header" style="background: #0c004e!important;">
                    <h5 class="modal-title" id="add-tournamentTitle"
                        style="color: #fff; font-size: 20px; font-weight: 800;margin:0px;">Add New Cricket Match</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        style="color: #fff;"></button>
                </div>

                <form action="{{ route('admin.cricket-matches.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="name" class="form-label">Match Title (Optional)</label>
                                <input type="text" name="title" class="form-control" placeholder="Enter Title">
                            </div>

                            <div class="col-md-6">
                                <label for="venue" class="form-label">Match Venue</label>
                                <input type="text" name="venue" class="form-control" placeholder="Enter venue or city">
                            </div>
                            <div class="col-md-6">
                                <label for="match_date" class="form-label">Match Date</label>
                                <input type="date" name="match_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="team_a_id" class="form-label">Team A</label>
                                <select name="team_a_id" class="form-select select2">
                                    <option value=""></option>
                                    @foreach (\App\Models\Team::all() as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="team_b_id" class="form-label">Team B</label>
                                <select name="team_b_id" class="form-select select2">
                                    <option value=""></option>
                                    @foreach (\App\Models\Team::all() as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="max_overs" class="form-label">Total Overs (Per Innings)</label>
                                <input type="number" name="max_overs" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label for="max_overs" class="form-label">Max Overs (Per Bowler)</label>
                                <input type="number" name="bowler_max_overs" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" class="form-select select2">
                                    {{-- <option value="live">Live</option> --}}
                                    <option value="upcoming">Upcoming</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="match_type" class="form-label">Match Type</label>
                                <select name="match_type" class="form-select select2">
                                    <option value="regular">Regular</option>
                                    <option value="tournament">Tournament</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="match_type" class="form-label">Tounament Name</label>
                                <select name="tournament_id" class="form-select select2">

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

            function loadCricketMatchCards() {
                $.ajax({
                    url: '{{ route('admin.cricket-matches.loader') }}',
                    type: 'GET',
                    success: function(res) {
                        const container = $('#matchesCardContainer');
                        container.empty();

                        if (!res.data || res.data.length === 0) {
                            container.html(`
                                <div class="col-12">
                                    <div class="alert alert-info text-center">
                                        No matches found
                                    </div>
                                </div>
                            `);
                            return;
                        }

                        res.data.forEach(match => {
                            container.append(renderMatchCard(match));
                        });
                    },
                    error: function(err) {
                        console.error('Error loading matches:', err);
                    }
                });
            }

            function renderMatchCard(match) {
                console.log("Match: ", match);
                const statusBadge = {
                    Upcoming: 'primary',
                    Live: 'warning',
                    Completed: 'success'
                } [match.status] || 'secondary';

                return `
                    <div class="col-12 col-md-6 col-xl-3 mb-3">
                        <div class="card match-card h-100">

                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="badge bg-${statusBadge}">
                                    ${match.status}
                                </span>
                                <small class="text-muted">
                                    ${match.match_date ?? ''}
                                </small>
                            </div>

                            <div class="card-body">

                                <!-- Match Title -->
                                <h6 class="fw-bold mb-2">
                                    <a href="${match.viewUrl}" class="text-decoration-none">
                                        ${match.title}
                                    </a>
                                </h6>

                                <!-- Teams & Scores -->
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span>${match.team_a}</span>
                                        <strong>${match.team_a_score ?? '-'}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>${match.team_b}</span>
                                        <strong>${match.team_b_score ?? '-'}</strong>
                                    </div>
                                </div>

                                <hr class="my-2">

                                <!-- Result / Schedule -->
                                <p class="mb-1 small">
                                    <strong>Result:</strong>
                                    ${match.result_summary ?? '<span class="text-muted">Not decided</span>'}
                                </p>

                                <p class="mb-1 small">
                                    <strong>Max Overs:</strong> ${match.max_overs}
                                </p>

                                <p class="mb-1 small">
                                    <strong>Venue:</strong> ${match.venue ?? '-'}
                                </p>
                            </div>

                            <!-- Actions -->
                            <div class="card-footer d-flex flex-wrap gap-2 justify-content-center">

                                ${match.canView ? `
                                                <a href="${match.viewUrl}" class="btn btn-sm btn-outline-primary">
                                                    View
                                                </a>` : ''}

                                ${match.canScore && match.status !== 'Completed' ? `
                                                <a href="${match.startUrl}" class="btn btn-sm btn-outline-warning">
                                                    Edit Score
                                                </a>` : ''}

                                ${match.canEdit && match.status !== 'Completed'? `
                                                <a href="${match.editUrl}" class="btn btn-sm btn-outline-secondary">
                                                    Edit Details
                                                </a>` : ''}

                                ${match.canDelete ? `
                                                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${match.id}">
                                                    Delete
                                                </button>` : ''}
                            </div>
                        </div>
                    </div>
                    `;
            }

            $('.select2').select2({
                width: "100%",
                dropdownParent: $('#add-cricket-match'),

            });

            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                new bootstrap.Tooltip(el);
            });

            const formatSelector = document.getElementById("filter_format");

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
                console.log(selectedId)
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
                                window.location.reload();
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

            loadCricketMatchCards();
        });
    </script>
@endpush
