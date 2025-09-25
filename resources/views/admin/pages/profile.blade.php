@extends('admin.layouts.theme')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card custom-card-border">
                <div class="card-body">
                    <form action="{{ route('admin.settings.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('POST')
                        <div class="border-bottom mb-3">
                            <div class="row">
                                <div class="col-md-12">
                                    <div>
                                        <h6 class="mb-3">General Informations</h6>
                                        <div
                                            class="d-flex align-items-center flex-wrap row-gap-3 bg-light w-100 rounded p-3 mb-4">
                                            <div class="d-flex align-items-center justify-content-center avatar avatar-xxl rounded-circle border border-dashed me-2 flex-shrink-0 text-dark frames"
                                                style="{{ $user->image ? "background-image: url('" . $user->image . "'); background-size: cover; background-repeat: no-repeat;" : '' }}">
                                                @if (!$user->image)
                                                    <i class="ti ti-photo text-gray-3 fs-16"></i>
                                                @endif
                                            </div>
                                            <div class="profile-upload">
                                                <div class="mb-2">
                                                    <h6 class="mb-1">Profile Photo</h6>
                                                    <p class="fs-12">Recommended image size is 100px x 100px & file max
                                                        size 1Mb</p>
                                                </div>
                                                <div class="profile-uploader d-flex align-items-center">
                                                    <div class="drag-upload-btn btn btn-sm btn-inf me-2">
                                                        Upload
                                                        <input type="file" class="form-control image-sign"
                                                            multiple="">
                                                    </div>
                                                    <a href="javascript:void(0);" class="btn btn-light btn-sm">Cancel</a>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label mb-md-0">Full Name</label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="general[full_name]"
                                                value="{{ $user->full_name }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label mb-md-0">Nick Name</label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="general[nickname]"
                                                value="{{ $user->nickname }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label mb-md-0">Email</label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="email" class="form-control" name="general[email]"
                                                value="{{ $user->email }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label mb-md-0">Username</label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="general[username]"
                                                value="{{ $user->username }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label mb-md-0">Phone</label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="general[phone]"
                                                value="{{ $user->phone }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label mb-md-0">National ID</label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="general[national_id]"
                                                value="{{ $user->national_id }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label mb-md-0">Blood Group</label>
                                        </div>
                                        <div class="col-md-8">
                                            <select name="general[blood_group]" class="select2 form-control">
                                                <option value="A+" @if ($user->blood_group == 'A+') selected @endif>A+
                                                </option>
                                                <option value="A-" @if ($user->blood_group == 'A-') selected @endif>A-
                                                </option>
                                                <option value="B+" @if ($user->blood_group == 'B+') selected @endif>B+
                                                </option>
                                                <option value="B-" @if ($user->blood_group == 'B-') selected @endif>B-
                                                </option>
                                                <option value="AB+" @if ($user->blood_group == 'AB+') selected @endif>AB+
                                                </option>
                                                <option value="AB-" @if ($user->blood_group == 'AB-') selected @endif>AB-
                                                </option>
                                                <option value="O+" @if ($user->blood_group == 'O+') selected @endif>O+
                                                </option>
                                                <option value="O-" @if ($user->blood_group == 'O-') selected @endif>O-
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label mb-md-0">Religion</label>
                                        </div>
                                        <div class="col-md-8">
                                            <select name="general[religion]" class="select2 form-control">
                                                <option @if ($user->religion == 'islam') selected @endif value="islam">
                                                    Islam</option>
                                                <option @if ($user->religion == 'hinduism') selected @endif
                                                    value="hinduism">Hinduism</option>
                                                <option @if ($user->religion == 'christianity') selected @endif
                                                    value="christianity">Christianity</option>
                                                <option @if ($user->religion == 'buddhism') selected @endif
                                                    value="buddhism">Buddhism</option>
                                                <option @if ($user->religion == 'others') selected @endif value="others">
                                                    Others</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label mb-md-0">Gender</label>
                                        </div>
                                        <div class="col-md-8">
                                            <select name="general[gender]" class="select2 form-control">
                                                <option @if ($user->gender == 'male') selected @endif value="male">
                                                    Male</option>
                                                <option @if ($user->gender == 'female') selected @endif value="female">
                                                    Female</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label mb-md-0">Date Of Birth</label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="general[date_of_birth]"
                                                id="flt-dob" value="{{ $user->date_of_birth }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label mb-md-0">Status</label>
                                        </div>
                                        <div class="col-md-8">
                                            <select name="general[status]" class="select2 form-control">
                                                <option value="active" @if ($user->status == 'active') selected @endif>
                                                    Active</option>
                                                <option value="inactive"
                                                    @if ($user->status == 'inactive') selected @endif>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label mb-md-0">Administrative Role</label>
                                        </div>
                                        <div class="col-md-8">
                                            <select name="general[role_id]" class="select2 form-control w-100">
                                                <option value=""></option>
                                                @foreach ($roles as $item)
                                                    <option value="{{ $item->id }}"
                                                        @if ($item->id == $user->role->id) selected @endif>
                                                        {{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-2">
                                            <label class="form-label mb-md-0">Address</label>
                                        </div>
                                        <div class="col-md-10">

                                            <input type="text" class="form-control" name="general[address]"
                                                value="{{ $user->address }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-bottom mb-3">
                            <h6 class="mb-3">Change Password</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label mb-md-0">New Password</label>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="pass-group">
                                                <input type="password" class="pass-inputs form-control">
                                                <span class="ti toggle-passwords ti-eye-off"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label mb-md-0">Confirm Password</label>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="pass-group">
                                                <input type="password" class="pass-inputa form-control">
                                                <span class="ti toggle-passworda ti-eye-off"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-center">
                            <button type="submit" class="btn btn-success text-uppercase">save changes</button>
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
            document.querySelector('#flt-dob').flatpickr({
                enableTime: false,
                dateFormat: 'Y-m-d'
            });

            $('.select2').select2({
                minimumResultsForSearch: Infinity
            });
        });
    </script>
@endpush
