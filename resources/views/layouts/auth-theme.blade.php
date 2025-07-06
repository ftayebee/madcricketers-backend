<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Title Meta -->
    <meta charset="utf-8" />
    <title>Sign In | {{ env('APP_NAME') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="A fully responsive premium admin dashboard template, Real Estate Management Admin Template" />
    <meta name="author" content="Techzaa" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <link rel="shortcut icon" href="{{ asset('storage/assets/images/favicon.ico') }}">
    <link href="{{ asset('storage/assets/css/vendor.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('storage/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('storage/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <script src="{{ asset('storage/assets/js/config.min.js') }}"></script>

</head>

<body class="authentication-bg">

    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">
            @yield('content')
        </div>
    </div>

    <script src="{{ asset('storage/assets/js/vendor.js') }}"></script>
    <script src="{{ asset('storage/assets/js/app.js') }}"></script>
</body>

</html>
