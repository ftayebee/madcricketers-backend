@extends('admin.layouts.theme')

@section('content')
    @push('styles')
        <style>
            .required-mark{
                font-size: 14px;
                color: red;
            }
        </style>
    @endpush
    <div class="row">
        <div class="col-sm-12">
            <form action="{{route('admin.settings.users.store')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card">
                    <div class="card-body" id="form-body">
                        <div class="row">
                            <div class="col-12">
                                <h5 class="pb-2 border-bottom mb-3">General Informations</h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="custom-file-container" data-upload-id="myFirstImage">
                                    <label for="profile_picture" class="form-label">Upload Profile Picture (Max size 1mb)</label>
                                    <input type="file" name="general[profile_picture]" id="profile_picture" class="dropify form-control" data-max-file-size="1M" data-allowed-file-extensions="jpg png jpeg"/>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" >Full Name <span class="required-mark">*</span></label>
                                <input type="text" class="form-control" placeholder="Full Name"  required="" name="general[full_name]">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" >Nickname <small>(Optional)</small></label>
                                <input type="text" class="form-control" placeholder="Nickname"  required="" name="general[nickname]">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" >Username <span class="required-mark">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" aria-label="Auto Generated Id" name="general[username]" id="username">
                                    <button class="btn btn-outline-secondary" type="button" id="btn-generateUsername">Generate</button>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" >Email Address <span class="required-mark">*</span></label>
                                <input type="text" class="form-control" placeholder="Email Address"  required="" name="general[email]">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" >Phone Number <small>(Has What's App Opened)</small><span class="required-mark">*</span></label>
                                <input type="text" class="form-control" placeholder="Phone Number"  required="" name="general[phone]">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" >National ID <small>(17 or 10 digits)</small><span class="required-mark">*</span></label>
                                <input type="text" class="form-control" placeholder="National ID Card Number"  required="" name="general[national_id]">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label" >Status <span class="required-mark">*</span></label>
                                <select class="select2 form-control" name="general[status]">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" >Role <span class="required-mark">*</span></label>
                                <select class="select2 form-control" name="general[role_id]">
                                    <option value=""></option>
                                    @foreach ($roles as $item)
                                        <option value="{{$item->id}}">{{ucfirst($item->name)}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label" >Blood Group</label>
                                <select class="select2 form-control" name="general[blood_group]">
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" >Religion</label>
                                <select class="select2 form-control" name="general[religion]">
                                    <option value="islam">Islam</option>
                                    <option value="hinduism">Hinduism</option>
                                    <option value="christianity">Christianity</option>
                                    <option value="buddhism">Buddhism</option>
                                    <option value="others">Others</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" >Gender</label>
                                <select class="select2 form-control" name="general[gender]">
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" >Date Of Birth</label>
                                <input type="text" class="form-control flatpickr-input" placeholder="YYYY-MM-DD" id="flt-dob" name="general[date_of_birth]">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" >Password</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" aria-label="Minimum 8 Digits" name="general[password]">
                                    <button class="btn btn-outline-secondary" type="button" id="btn-generatePassword">Generate</button>
                                </div>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label" >Address</label>
                                <input type="text" class="form-control" placeholder="Present Address"  required="" name="general[address]">
                            </div>
                        </div>

                        <div class="row" id="player-info" style="display: none;">
                            <div class="col-md-3">
                                <label class="form-label" >Player Type</label>
                                <select class="select2 form-control" name="player[type]">
                                    <option value="">Choose</option>
                                    <option value="registered">Regular</option>
                                    <option value="guest">Guest</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" >Player Role</label>
                                <select class="select2 form-control" name="player[role]">
                                    <option value="">Choose</option>
                                    <option value="batsman">Batsman</option>
                                    <option value="bowler">Bowler</option>
                                    <option value="all-rounder">All Rounder</option>
                                    <option value="wicketkeeper">Wicket Keeper</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" >Batting Style</label>
                                <select class="select2 form-control" name="player[batting_style]">
                                    <option value="">Choose</option>
                                    <option value="right-handed">Right Handed</option>
                                    <option value="left-handed">Left Handed</option>
                                    <option value="switch hitter">Switch Hitter</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" >Bowling Style</label>
                                <select class="select2 form-control" name="player[bowling_style]">
                                    <option value="fast">Fast</option>
                                    <option value="medium">Meidum</option>
                                    <option value="spin">Spin</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-3" id="form-btn-container">
                            <div class="col-md-3 m-auto">
                                <input type="hidden" name="hasPlayerInfo" value="false">
                                <button type="submit" class="btn btn-success w-100 text-uppercase">save details</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function(){
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

            $('#btn-generatePassword').on('click', function () {
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
                        input.after('<div class="invalid-feedback">National ID must be exactly 10 or 17 digits.</div>');
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
                        $(this).after('<div class="invalid-feedback">Enter a valid 11-digit phone number (e.g., 01XXXXXXXXX).</div>');
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
                        input.after('<div class="invalid-feedback">Please enter a valid email address.</div>');
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
                        input.after('<div class="invalid-feedback">Password must be exactly 8 characters long.</div>');
                    }
                } else {
                    input.removeClass('is-invalid');
                    input.next('.invalid-feedback').remove();
                }
            });

            $('select[name="general[role_id]"]').on('change', function(){
                const selectedValue = $(this).val();
                $('#player-info').hide();
                $('input[name="hasPlayerInfo"]').val(false);
                if(selectedValue == 3){
                    $('#player-info').show();
                    $('input[name="hasPlayerInfo"]').val(true);
                    initSelect2();
                }
            })
        });
    </script>
@endpush
