<!DOCTYPE html>
<html lang="en">

<head>
    @include('player.includes.header')
    @include('player.includes.css')
</head>

<body>

    <!-- START Wrapper -->
    <div class="wrapper">

        <!-- ========== Topbar Start ========== -->
        @include('player.includes.topbar')

        <!-- ========== App Menu Start ========== -->
        @include('player.includes.sidebar')
        <!-- ========== App Menu End ========== -->

        <!-- ==================================================== -->
        <!-- Start right Content here -->
        <!-- ==================================================== -->
        <div class="page-content" >

            <!-- Start Container Fluid -->
            <div class="container-fluid">
                <!-- Start here.... -->
                <!-- ========== Page Title Start ========== -->
                @include('player.includes.breadcrumb')
                <!-- ========== Page Title End ========== -->
                @yield('content')
            </div>
            <!-- End Container Fluid -->

            <!-- ========== Footer Start ========== -->
            @include('player.includes.footer')
            <!-- ========== Footer End ========== -->

        </div>
        <!-- ==================================================== -->
        <!-- End Page Content -->
        <!-- ==================================================== -->

    </div>
    <!-- END Wrapper -->

    @include('player.includes.scripts')
</body>

</html>
