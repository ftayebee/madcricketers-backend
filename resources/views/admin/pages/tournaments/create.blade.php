@extends('admin.layouts.theme')

@section('content')
    @push('styles')
        <style>
            .cs-label-color{
                color: #002450;
            }
            .required-mark{
                color: red;
                font-size: 16px;
                font-weight: 800;
            }
        </style>
    @endpush
    <div class="row">
        <div class="col-sm-12">
            <div class="card custom-card-border">
                <div class="card-header bg-soft-cyan">
                    <h5 class="text-center fs-24 fw-bold m-0">Insert Tournament Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tournaments.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fs-16 cs-label-color">Tournament Name <span class="required-mark">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        placeholder="Enter tournament name" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="location" class="form-label fs-16 cs-label-color">Location </label>
                                    <input type="text" name="location" class="form-control"
                                        placeholder="Enter venue or city">
                                </div>

                                <div class="col-md-3">
                                    <label for="start_date" class="form-label fs-16 cs-label-color">Start Date <span class="required-mark">*</span></label>
                                    <input type="date" name="start_date" class="form-control">
                                </div>

                                <div class="col-md-3">
                                    <label for="end_date" class="form-label fs-16 cs-label-color">End Date <span class="required-mark">*</span></label>
                                    <input type="date" name="end_date" class="form-control">
                                </div>

                                <div class="col-md-3">
                                    <label for="status" class="form-label fs-16 cs-label-color">Status <span class="required-mark">*</span></label>
                                    <select name="status" class="form-select">
                                        <option value="upcoming">Upcoming</option>
                                        <option value="ongoing">Ongoing</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="max_overs" class="form-label fs-16 cs-label-color">Overs Per Innings <span class="required-mark">*</span></label>
                                    <input type="number" name="overs_per_innings" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label for="format" class="form-label fs-16 cs-label-color">Format <span class="required-mark">*</span></label>
                                    <select name="format" class="form-select" id="format">
                                        <option value=""></option>
                                        <option value="group">Group</option>
                                        <option value="round-robin">Round Robin</option>
                                        <option value="knockout">Knockout</option>
                                    </select>
                                </div>

                                <div class="col-md-6 format-dependent" id="group-fields" style="display: none;">
                                    <label for="number_of_groups" class="form-label fs-16 cs-label-color">Number of Groups <span class="required-mark">*</span></label>
                                    <input type="number" name="group_count" class="form-control" min="2"
                                        step="2">
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label for="status" class="form-label fs-16 cs-label-color">Team List <span class="required-mark">*</span></label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input checkbox-md" type="checkbox" role="switch" id="flexSwitchCheckChecked" checked="" name="seperate_teams">
                                            <label class="form-check-label" for="flexSwitchCheckChecked">Seperate Teams to Groups</label>
                                        </div>
                                    </div>
                                    <select name="team_id[]" class="form-select form-control" multiple>
                                        @foreach ($validTeams as $teamInfo)
                                        <option value="{{$teamInfo->id}}">{{$teamInfo->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="trophy_image" class="form-label fs-16 cs-label-color">Trophy Image</label>
                                    <input type="file" name="trophy_image" class="form-control" accept="image/*">
                                </div>

                                <div class="col-md-6">
                                    <label for="logo" class="form-label fs-16 cs-label-color">Tournament Logo</label>
                                    <input type="file" name="logo" class="form-control" accept="image/*">
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label fs-16 cs-label-color">Description</label>
                                    <textarea name="description" rows="3" class="form-control" placeholder="Write tournament description..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary">Save Tournament</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('select[name="team_id[]"]').select2({
                width: "100%",
                closeOnSelect: false
            });

            document.getElementById("format").addEventListener("change", () => {
                const value = formatSelector.value;
                console.log(value)
                document.querySelectorAll(".format-dependent").forEach(el => el.style.display = "none");

                if (value === "group") {
                    document.getElementById("group-fields").style.display = "block";
                }
                // else if (value === "knockout") {
                //     document.getElementById("knockout-fields").style.display = "block";
                // }
            });
        });
    </script>
@endpush
