@extends('layouts.auth-theme')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5">
                <div class="card auth-card">
                    <div class="card-body px-3 py-5">
                        <div class="mx-auto mb-4 text-center auth-logo">
                            <a href="index.html" class="logo-dark">
                                <img src="{{ asset('storage/assets/images/logo-dark.png') }}" height="32" alt="logo dark">
                            </a>

                            <a href="index.html" class="logo-light">
                                <img src="{{ asset('storage/assets/images/logo-light.png') }}" height="28" alt="logo light">
                            </a>
                        </div>

                        <h2 class="fw-bold text-uppercase text-center fs-18">Reset Password</h2>
                        <p class="text-muted text-center mt-1 mb-4">Enter your email address and we'll send you an email
                            with instructions <br> to reset your password.</p>

                        <div class="px-4">
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif
                            <form action="{{ route('password.email') }}" class="authentication-form" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label" for="example-email">Email</label>
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
                                <div class="mb-1 text-center d-grid">
                                    <button class="btn btn-info py-2 fw-medium" type="submit">{{ __('Send Password Reset Link') }}</button>
                                </div>
                            </form>
                        </div> <!-- end col -->
                    </div> <!-- end card-body -->
                </div> <!-- end card -->
                <p class="mb-0 text-center text-dark">Back to <a href="auth-signin.html"
                        class="text-reset text-unline-dashed fw-bold ms-1">Sign In</a></p>
            </div> <!-- end col -->
        </div>
    </div>
@endsection
