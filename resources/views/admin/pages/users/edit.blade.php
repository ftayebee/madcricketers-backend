@extends('admin.layouts.theme')

@section('content')
    @push('styles')
        <style>
            .required-mark {
                font-size: 14px;
                color: red;
            }

            /* Hide the default radio input */
            .form-radio {
                display: none;
            }

            /* Custom radio wrapper */
            .form-radio+span {
                display: inline-block;
                padding: 0.5rem 1rem;
                border: 2px solid #ccc;
                border-radius: 4px;
                /* fully rounded */
                cursor: pointer;
                transition: all 0.2s ease;
                font-weight: 500;
                user-select: none;
            }

            /* Hover effect */
            .form-radio+span:hover {
                border-color: #61CE70;
                background-color: rgba(97, 206, 112, 0.1);
            }

            /* Checked state */
            .form-radio:checked+span {
                background-color: #61CE70;
                /* brand color */
                color: white;
                border-color: #61CE70;
            }
        </style>
    @endpush
    <div class="row">
        <div class="col-sm-12 p-3" style="background: #fff;border-radius: 8px;box-shadow: 0 0 10px 2px #19183b1a;">
            <form action="{{ route('admin.settings.users.update', $user->id) }}" method="post"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="redirect"
                    value="{{ $user->hasRole('player') ? route('admin.players.index') : route('admin.settings.users.index') }}">
                <div class="card custom-card-border">
                    <div class="card-body" id="form-body">
                        <div class="row">
                            <div class="col-12">
                                <h5 class="pb-2 border-bottom mb-3" style="color: #00610d;font-size: 20px; font-weight: bold;border-color: #61CE70!important;">General Informations</h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <!-- Profile Picture Upload -->
                                <div class="form-group">
                                    <label style="font-size: 16px;font-weight: bold;" for="profile_picture">Upload Profile Picture</label>
                                    <div class="row">
                                        <div class="col-10">
                                            <input type="file" id="profile_picture" accept="image/*" class="form-control">
                                            <input type="hidden" name="general[profile_picture]" id="image_base64">
                                        </div>
                                        <div class="col-2">
                                            <img id="preview-cropped" src="{{ $user->image ?? '' }}"
                                                style="max-width: 160px; border:1px solid #ddd; display: {{ $user->image ? 'block' : 'none' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">Full Name <span class="required-mark">*</span></label>
                                <input type="text" class="form-control" placeholder="Full Name" required=""
                                    name="general[full_name]" value="{{ $user->full_name }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">Nickname <small>(Optional)</small></label>
                                <input type="text" class="form-control" placeholder="Nickname" required=""
                                    name="general[nickname]" value="{{ $user->nickname }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">Email Address <span class="required-mark">*</span></label>
                                <input type="text" class="form-control" placeholder="Email Address"
                                    required="" name="general[email]" value="{{ $user->email }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">Phone Number <small>(Has What's App Opened)</small><span
                                        class="required-mark">*</span></label>
                                <input type="text" class="form-control" placeholder="Phone Number" required=""
                                    name="general[phone]" value="{{ $user->phone }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">National ID <small>(17 or 10 digits)</small><span
                                        class="required-mark">*</span></label>
                                <input type="text" class="form-control" placeholder="National ID Card Number"
                                    required="" name="general[national_id]" value="{{ $user->national_id }}">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">Status <span class="required-mark">*</span></label>
                                <select class="select2 form-control" name="general[status]">
                                    <option @if ($user->status == 'active') 'selected' @endif value="active">
                                        Active
                                    </option>
                                    <option @if ($user->status == 'inactive') 'selected' @endif value="inactive">
                                        Inactive
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">Role <span class="required-mark">*</span></label>
                                <select class="select2 form-control" name="general[role_id]">
                                    <option value=""></option>
                                    @foreach ($roles as $item)
                                        <option value="{{ $item->id }}"
                                            @if ($user->hasRole($item->id)) selected @endif>
                                            {{ ucfirst($item->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">Blood Group</label>
                                <select class="select2 form-control" name="general[blood_group]">
                                    <option @if ($user->blood_group == 'A+') selected @endif value="A+">A+
                                    </option>
                                    <option @if ($user->blood_group == 'A-') selected @endif value="A-">A-
                                    </option>
                                    <option @if ($user->blood_group == 'B+') selected @endif value="B+">B+
                                    </option>
                                    <option @if ($user->blood_group == 'A-') selected @endif value="B-">B-
                                    </option>
                                    <option @if ($user->blood_group == 'AB+') selected @endif value="AB+">AB+
                                    </option>
                                    <option @if ($user->blood_group == 'AB-') selected @endif value="AB-">AB-
                                    </option>
                                    <option @if ($user->blood_group == 'O+') selected @endif value="O+">O+
                                    </option>
                                    <option @if ($user->blood_group == 'O-') selected @endif value="O-">O-
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">Religion</label>
                                <select class="select2 form-control" name="general[religion]">
                                    <option @if ($user->religion == 'islam') selected @endif value="islam">
                                        Islam
                                    </option>
                                    <option @if ($user->religion == 'hinduism') selected @endif value="hinduism">
                                        Hinduism
                                    </option>
                                    <option @if ($user->religion == 'christianity') selected @endif
                                        value="christianity">
                                        Christianity</option>
                                    <option @if ($user->religion == 'buddhism') selected @endif value="buddhism">
                                        Buddhism
                                    </option>
                                    <option @if ($user->religion == 'others') selected @endif value="others">
                                        Others
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">Gender</label>
                                <select class="select2 form-control" name="general[gender]">
                                    <option @if ($user->gender == 'male') selected @endif value="male">Male
                                    </option>
                                    <option @if ($user->gender == 'female') selected @endif value="female">
                                        Female
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">Date Of Birth</label>
                                <input type="text" class="form-control flatpickr-input"
                                    placeholder="YYYY-MM-DD" id="flt-dob" name="general[date_of_birth]"
                                    value="{{ old('general.date_of_birth', $user->date_of_birth ?? '') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" aria-label="Minimum 8 Digits"
                                        name="general[password]">
                                    <button class="btn btn-outline-secondary" type="button"
                                        id="btn-generatePassword">Generate</button>
                                </div>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">Address</label>
                                <input type="text" class="form-control" placeholder="Present Address"
                                    required="" name="general[address]"
                                    value="{{ old('general.address', $user->address) }}">
                            </div>
                        </div>

                        <div class="row" id="player-info"
                            style="display: @if ($user->hasRole($item->id)) flex @else none @endif;">
                            <div class="col-md-3 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">Player Type</label>
                                <select class="select2 form-control" name="player[player_type]">
                                    <option @if ($user->player->player_type == '') selected @endif value="">
                                        Choose
                                    </option>
                                    <option @if ($user->player->player_type == 'registered') selected @endif value="registered">
                                        Registered</option>
                                    <option @if ($user->player->player_type == 'guest') selected @endif value="guest">
                                        Guest
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">Player Role</label>
                                <select class="select2 form-control" name="player[player_role]">
                                    <option value="">Choose</option>
                                    <option @if ($user->player->player_role == 'batsman') selected @endif value="batsman">
                                        Batsman
                                    </option>
                                    <option @if ($user->player->player_role == 'bowler') selected @endif value="bowler">
                                        Bowler
                                    </option>
                                    <option @if ($user->player->player_role == 'all-rounder') selected @endif
                                        value="all-rounder">All
                                        Rounder</option>
                                    <option @if ($user->player->player_role == 'wicketkeeper') selected @endif
                                        value="wicketkeeper">Wicket
                                        Keeper</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">Batting Style</label>
                                <select class="select2 form-control" name="player[batting_style]">
                                    <option value="">Choose</option>
                                    <option @if ($user->player->batting_style == 'right-handed') selected @endif
                                        value="right-handed">Right
                                        Handed</option>
                                    <option @if ($user->player->batting_style == 'left-handed') selected @endif
                                        value="left-handed">Left
                                        Handed</option>
                                    <option @if ($user->player->batting_style == 'switch hitter') selected @endif
                                        value="switch hitter">
                                        Switch Hitter</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">Bowling Style</label>
                                <select class="select2 form-control" name="player[bowling_style]">
                                    <option value="">Choose</option>
                                    <option @if ($user->player->bowling_style == 'fast') selected @endif value="fast">Fast
                                    </option>
                                    <option @if ($user->player->bowling_style == 'medium') selected @endif value="medium">
                                        Meidum
                                    </option>
                                    <option @if ($user->player->bowling_style == 'spin') selected @endif value="spin">Spin
                                    </option>
                                    <option @if ($user->player->bowling_style == 'none') selected @endif value="none">None
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">Jursey Number</small><span
                                        class="required-mark">*</span></label>
                                <input type="text" class="form-control" placeholder="Jursey Number"
                                    required="" name="player[jursey_number]"
                                    value="{{ $user->player->jursey_number }}">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">Jursey Name</small><span
                                        class="required-mark">*</span></label>
                                <input type="text" class="form-control" placeholder="Jursey Name"
                                    required="" name="player[jursey_name]"
                                    value="{{ $user->player->jursey_name }}">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="block font-medium form-label">Jersey Size</label>
                                <div class="flex space-x-4">
                                    <label style="font-size: 16px;font-weight: bold;"class="flex items-center space-x-2">
                                        <input type="radio" value="s" name="player[jursey_size]"
                                            class="form-radio" />
                                        <span>S</span>
                                    </label>
                                    <label style="font-size: 16px;font-weight: bold;"class="flex items-center space-x-2">
                                        <input type="radio" value="m" name="player[jursey_size]"
                                            class="form-radio" />
                                        <span>M</span>
                                    </label>
                                    <label style="font-size: 16px;font-weight: bold;"class="flex items-center space-x-2">
                                        <input type="radio" value="l"name="player[jursey_size]"
                                            class="form-radio" />
                                        <span>L</span>
                                    </label>
                                    <label style="font-size: 16px;font-weight: bold;"class="flex items-center space-x-2">
                                        <input type="radio" value="xl"name="player[jursey_size]"
                                            class="form-radio" />
                                        <span>XL</span>
                                    </label>
                                    <label style="font-size: 16px;font-weight: bold;"class="flex items-center space-x-2">
                                        <input type="radio" value="2xl"name="player[jursey_size]"
                                            class="form-radio" />
                                        <span>2XL</span>
                                    </label>
                                    <label style="font-size: 16px;font-weight: bold;"class="flex items-center space-x-2">
                                        <input type="radio" value="3xl"name="player[jursey_size]"
                                            class="form-radio" />
                                        <span>3XL</span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label style="font-size: 16px;font-weight: bold;"class="form-label">Chest Measurement</small><span
                                        class="required-mark">*</span></label>
                                <input type="text" class="form-control" placeholder="Chest Measurement"
                                    required="" name="player[chest_measurement]"
                                    value="{{ $user->player->chest_measurement }}">
                            </div>
                        </div>

                        <div class="row mt-3" id="form-btn-container">
                            <div class="col-md-3 m-auto">
                                <input type="hidden" name="hasPlayerInfo" value="false">
                                <button type="submit" class="btn btn-success w-100 text-uppercase">save
                                    details</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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
            const initSelect2 = () => {
                $('.select2').select2({
                    minimumResultsForSearch: Infinity
                });
            }

            initSelect2();

            document.querySelector('#flt-dob').flatpickr({
                enableTime: false,
                dateFormat: 'Y-m-d'
            });

            $('.emp-joinDate').flatpickr({
                enableTime: false,
                dateFormat: 'Y-m-d'
            });

            $('#btn-generatePassword').on('click', function() {
                const minLength = 8;
                let password = '';

                for (let i = 0; i < minLength; i++) {
                    password += Math.floor(Math.random() * 10); // 0-9 digits
                }

                $('input[name="general[password]"]').val(password);
            });

            $('input[name="general[national_id]"]').on('blur', function() {
                var input = $(this);
                var value = input.val().trim();

                if (!/^\d{10}$/.test(value) && !/^\d{17}$/.test(value)) {
                    input.addClass('is-invalid');
                    if (input.next('.invalid-feedback').length === 0) {
                        input.after(
                            '<div class="invalid-feedback">National ID must be exactly 10 or 17 digits.</div>'
                        );
                    }
                } else {
                    input.removeClass('is-invalid');
                    input.next('.invalid-feedback').remove();
                }
            });

            $('input[name="general[phone]"]').on('blur', function() {
                var phone = $(this).val().trim();

                if (/^01\d{9}$/.test(phone)) {
                    $(this).removeClass('is-invalid');
                } else {
                    $(this).addClass('is-invalid');
                    if ($(this).next('.invalid-feedback').length === 0) {
                        $(this).after(
                            '<div class="invalid-feedback">Enter a valid 11-digit phone number (e.g., 01XXXXXXXXX).</div>'
                        );
                    }
                }
            });

            $('input[name="general[email]"]').on('blur', function() {
                var input = $(this);
                var email = input.val().trim();

                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!emailPattern.test(email)) {
                    input.addClass('is-invalid');
                    if (input.next('.invalid-feedback').length === 0) {
                        input.after(
                            '<div class="invalid-feedback">Please enter a valid email address.</div>');
                    }
                } else {
                    input.removeClass('is-invalid');
                    input.next('.invalid-feedback').remove();
                }
            });

            $('input[name="general[password]"]').on('blur', function() {
                var input = $(this);
                var password = input.val().trim();

                if (password.length !== 8) {
                    input.addClass('is-invalid');
                    if (input.next('.invalid-feedback').length === 0) {
                        input.after(
                            '<div class="invalid-feedback">Password must be exactly 8 characters long.</div>'
                        );
                    }
                } else {
                    input.removeClass('is-invalid');
                    input.next('.invalid-feedback').remove();
                }
            });

            $('select[name="general[role_id]"]').on('change', function() {
                const selectedValue = $(this).val();
                $('#player-info').hide();
                $('input[name="hasPlayerInfo"]').val(false);
                if (selectedValue == 3) {
                    $('#player-info').show();
                    $('input[name="hasPlayerInfo"]').val(true);
                    initSelect2();
                }
            })
        });
    </script>

    <script>
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
    </script>
@endpush
