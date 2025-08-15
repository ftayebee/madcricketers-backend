@extends('layouts.auth-theme')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5">
                <div class="card auth-card">
                    <div class="card-body px-3 py-5">
                        <div class="mx-auto mb-4 text-center auth-logo">
                            <a href="index.html" class="logo-dark">
                                <img src="{{ asset('storage/assets/images/main-logo.png') }}" height="42" alt="logo dark">
                            </a>

                            <a href="index.html" class="logo-light">
                                <img src="{{ asset('storage/assets/images/logo-light.png') }}" height="28" alt="logo light">
                            </a>
                        </div>

                        <h2 class="fw-bold text-uppercase text-center fs-18">Reset Password</h2>

                        <div class="px-4">
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif
                            <form method="POST" action="{{ route('password.update') }}">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">
                                <div class="mb-3">
                                    <label class="form-label" for="example-email">{{ __('Email Address') }}</label>
                                    <input type="email" id="email" @error('email') is-invalid @enderror" name="email"
                                        value="{{ old('email') }}" required autocomplete="email" autofocus
                                        class="form-control bg-light bg-opacity-50 border-light py-2"
                                        placeholder="Enter your email">

                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="example-email">{{ __('Password') }}</label>
                                    <input id="password" type="password" class="form-control bg-light bg-opacity-50 border-light py-2 @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="example-email">{{ __('Confirm Password') }}</label>
                                    <input id="password-confirm" type="password" class="form-control bg-light bg-opacity-50 border-light py-2" name="password_confirmation" required autocomplete="new-password">
                                </div>
                                <div class="mb-1 text-center d-grid">
                                    <button class="btn btn-info py-2 fw-medium" type="submit">{{ __('Reset Password') }}</button>
                                </div>
                            </form>
                        </div> <!-- end col -->
                    </div> <!-- end card-body -->
                </div> <!-- end card -->
            </div> <!-- end col -->
        </div>
    </div>
@endsection
