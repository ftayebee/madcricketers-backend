@extends('layouts.auth-theme')

@section('content')
<div class="auth-minimal-inner">
    <div class="minimal-card-wrapper">
        <div class="card mb-4 mt-5 mx-4 mx-sm-0 position-relative">
            <div class="wd-50 bg-white p-2 rounded-circle shadow-lg position-absolute translate-middle top-0 start-50">
                <img src="{{asset('storage/assets/images/logo-abbr.png')}}" alt="" class="img-fluid">
            </div>
            <div class="card-body p-sm-5">
                <h2 class="fs-20 fw-bolder mb-4">Login</h2>
                <h4 class="fs-13 fw-bold mb-2">Login to your account</h4>
                <p class="fs-12 fw-medium text-muted">Thank you for get back <strong>Nelel</strong> web applications, let's access our the best recommendation for you.</p>
                <form action="{{ route('login') }}" class="w-100 mt-4 pt-2" method="POST">
                    @csrf
                    <div class="mb-4">
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Email or Username">

                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="remember" id="rememberMe" {{ old('remember') ? 'checked' : '' }}>

                                <label class="custom-control-label c-pointer" for="rememberMe">Remember Me</label>
                            </div>
                        </div>
                        <div>
                            @if (Route::has('password.request'))
                                <a class="fs-11 text-primary" href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="mt-5">
                        <button type="submit" class="btn btn-lg btn-primary w-100">
                            {{ __('Login') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
