<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Title Meta -->
    <meta charset="utf-8" />
    <title>Sign In | {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MadCricketers – A live cricket score and financial data management tool, fully built with Laravel by AlgoHive IT." />
    <meta name="author" content="AlgoHive IT" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <link rel="shortcut icon" href="{{ asset('storage/assets/images/main-favicon.png') }}">
    <link href="{{ asset('storage/assets/css/vendor.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('storage/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('storage/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <script src="{{ asset('storage/assets/js/config.min.js') }}"></script>

</head>

<body class="authentication-bg" style="background: url('{{ asset('storage/assets/images/bg-home.png') }}');">

    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-5">
                    <div class="card auth-card">
                        <div class="card-body px-3 py-5">
                            <div class="mx-auto mb-4 text-center auth-logo">
                                <a href="https://madcricketers.com" class="logo-dark">
                                    <img src="{{ asset('storage/assets/images/main-logo-dark.png') }}" height="62" alt="logo dark">
                                </a>

                                <a href="index.html" class="logo-light">
                                    <img src="{{ asset('storage/assets/images/main-logo-light.png') }}" height="28" alt="logo light">
                                </a>
                            </div>
                            @yield('content')
                        </div> <!-- end card-body -->
                    </div> <!-- end card -->
                </div> <!-- end col -->
            </div>
        </div>
    </div>

    <script src="{{ asset('storage/assets/js/vendor.js') }}"></script>
    <script src="{{ asset('storage/assets/js/app.js') }}"></script>
</body>

</html>
