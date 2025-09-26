@extends('layouts.auth-theme')

@section('content')
    <h2 class="fw-bold text-uppercase text-center fs-18">Sign In</h2>
    <p class="text-muted text-center mt-1 mb-4">Enter your email address and password to access admin panel.</p>

    <div class="px-4">
        <form action="{{ route('login') }}" class="authentication-form" method="POST" id="login-form">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="example-email">Email</label>
                <input id="email" type="email" class="form-control bg-light bg-opacity-50 border-light py-2 @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Email or Username">

                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="mb-3">
                @if (Route::has('password.request'))
                    <a class="float-end text-muted text-unline-dashed ms-1" href="{{ route('password.request') }}">
                        {{ __('Forgot Your Password?') }}
                    </a>
                @endif
                <label class="form-label" for="example-password">Password</label>

                <input id="password" type="password" class="form-control bg-light bg-opacity-50 border-light py-2 @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="checkbox-signin" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="checkbox-signin">Remember me</label>
                </div>
            </div>

            <div class="mb-1 text-center d-grid">
                <button type="submit" class="btn btn-danger py-2 fw-medium">
                    {{ __('Login') }}
                </button>
            </div>
        </form>
    </div>
@endsection
