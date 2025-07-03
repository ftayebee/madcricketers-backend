<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="dark" data-bs-theme="light">

<head>
    @include('admin.includes.header')
    @include('admin.includes.css')
</head>

<body>
    @include('admin.includes.sidebar')

    @include('admin.includes.topbar')

    <main class="nxl-container">
        <div class="nxl-content">
            @include('admin.includes.breadcrumb')

            <div class="main-content">
                @yield('content')
            </div>
        </div>
        @include('admin.includes.footer')
    </main>

    @include('admin.includes.scripts')
</body>
<!--end body-->

</html>
