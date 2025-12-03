@extends('player.layouts.theme')

@section('content')
    @push('styles')
        <style>
            label{
                font-size: 16px!important;
                font-weight: bold!important;
            }
            .required-mark {
                font-size: 14px;
                color: red;
            }

            .btn-jursey {
                border: 1px solid #ddd;
                border-radius: 30px;
                padding: 8px 18px;
                font-weight: 500;
                background-color: #f8f9fa;
                color: #555;
                transition: all 0.3s ease;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            }

            .btn-check:checked+.btn-jursey {
                background-color: #34c759;
                /* iPhone green */
                color: #fff;
                border-color: #34c759;
                box-shadow: 0 0 10px rgba(52, 199, 89, 0.4);
            }

            .btn-jursey:hover {
                background-color: #e9ecef;
            }

            .jersey-size-group {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            .btn-check:checked+.btn-jursey {
                transform: scale(1.05);
            }

            .form-control {
                border-color: #c2c2c2;
            }
        </style>
    @endpush

    <div class="row">
        <div class="col-sm-12">
            <div class="card custom-card-border">
                <div class="card-body">
                    <form action="{{ route('player.profile.update', $user->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('POST')
                        <div class="border-bottom mb-3">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="profile-upload">
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-2">
                                                    <label style="font-size: 16px;font-weight: bold;margin-bottom: 10px!important;" for="profile_picture">Current Image</label>
                                                    <img id="preview-cropped" src="{{ $user->image ?? '' }}"
                                                    style="max-width: 75%; border:1px solid #ddd; display: {{ $user->image ? 'block' : 'none' }}">
                                                </div>
                                                <div class="col-10">
                                                    <label style="font-size: 16px;font-weight: bold;margin-bottom: 10px!important;" for="profile_picture">Upload Profile Picture</label>
                                                    <input type="file" id="profile_picture" accept="image/*"
                                                        class="form-control">
                                                    <input type="hidden" name="profile_image" id="image_base64">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                                <option value="AB+" @if ($user->blood_group == 'AB+') selected @endif>
                                                    AB+
                                                </option>
                                                <option value="AB-" @if ($user->blood_group == 'AB-') selected @endif>
                                                    AB-
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
                                                <option @if ($user->religion == 'hinduism') selected @endif value="hinduism">
                                                    Hinduism</option>
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
                                            <label class="form-label mb-md-0">Batting Style</label>
                                        </div>
                                        <div class="col-md-8">
                                            <select name="general[batting_style]" class="select2 form-control">
                                                <option value="right-handed"
                                                    @if ($user->player->batting_style == 'right-handed') selected @endif>Right Handed
                                                </option>
                                                <option value="left-handed"
                                                    @if ($user->player->batting_style == 'left-handed') selected @endif>Left Handed</option>
                                                <option value="switch hitter"
                                                    @if ($user->player->batting_style == 'switch hitter') selected @endif>Switch Hitter
                                                </option>
                                            </select>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label mb-md-0">Bowling Style</label>
                                        </div>
                                        <div class="col-md-8">
                                            <select name="general[bowling_style]" class="select2 form-control">
                                                <option value="fast" @if ($user->player->bowling_style == 'fast') selected @endif>
                                                    Fast</option>
                                                <option value="medium" @if ($user->player->bowling_style == 'medium') selected @endif>
                                                    Medium</option>
                                                <option value="spin" @if ($user->player->bowling_style == 'spin') selected @endif>
                                                    Spin</option>
                                                <option value="none" @if ($user->player->bowling_style == 'none') selected @endif>
                                                    None</option>
                                            </select>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label mb-md-0">Jursey Name</label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="general[jursey_name]"
                                                value="{{ $user->player->jursey_name }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label mb-md-0">Jursey Number</label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="general[jursey_number]"
                                                value="{{ $user->player->jursey_number }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label mb-md-0">Jursey Size</label>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="d-flex flex-wrap gap-2 jersey-size-group">
                                                <input type="radio" class="btn-check" name="general[jursey_size]"
                                                    id="sizeS" value="S" autocomplete="off">
                                                <label class="btn btn-jursey" for="sizeS">S</label>

                                                <input type="radio" class="btn-check" name="general[jursey_size]"
                                                    id="sizeM" value="M" autocomplete="off">
                                                <label class="btn btn-jursey" for="sizeM">M</label>

                                                <input type="radio" class="btn-check" name="general[jursey_size]"
                                                    id="sizeL" value="L" autocomplete="off">
                                                <label class="btn btn-jursey" for="sizeL">L</label>

                                                <input type="radio" class="btn-check" name="general[jursey_size]"
                                                    id="sizeXL" value="XL" autocomplete="off">
                                                <label class="btn btn-jursey" for="sizeXL">XL</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label mb-md-0">Chest Measurement</label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="general[chest_measurement]"
                                                value="{{ $user->player->chest_measurement }}">
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
                            <h4 class="mb-3 fs-20 font-weight-bold">Change Password</h4>
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

    <!-- Cropping Modal -->
    <div class="modal fade" id="cropModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crop Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div>
                        <img id="cropper-image" style="max-width: 100%;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="cropBtn">Crop</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#profile_picture').dropify();
            document.querySelector('#flt-dob').flatpickr({
                enableTime: false,
                dateFormat: 'Y-m-d'
            });

            $('.select2').select2({
                minimumResultsForSearch: Infinity
            });

            let cropper;
            const input = document.getElementById('profile_picture');
            const modal = new bootstrap.Modal(document.getElementById('cropModal'));
            const cropperImage = document.getElementById('cropper-image');
            const previewCropped = document.getElementById('preview-cropped');
            const hiddenInput = document.getElementById('image_base64');

            // File input change
            input.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (!file) return;

                const url = URL.createObjectURL(file);
                cropperImage.src = url;
                modal.show();

                // Wait until modal shown to init cropper
                document.getElementById('cropModal').addEventListener('shown.bs.modal', () => {
                    if (cropper) cropper.destroy();
                    cropper = new Cropper(cropperImage, {
                        aspectRatio: 1,
                        viewMode: 2,
                    });
                }, {
                    once: true
                });
            });

            // Crop button
            document.getElementById('cropBtn').addEventListener('click', () => {
                const canvas = cropper.getCroppedCanvas({
                    width: 300,
                    height: 300
                });
                canvas.toBlob((blob) => {
                    const reader = new FileReader();
                    reader.onloadend = () => {
                        const base64 = reader.result;
                        hiddenInput.value = base64;
                        previewCropped.src = base64;
                        previewCropped.style.display = 'block';
                        modal.hide();
                    };
                    reader.readAsDataURL(blob);
                });
            });
        });
    </script>
@endpush
