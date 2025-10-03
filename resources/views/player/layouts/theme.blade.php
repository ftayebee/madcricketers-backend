<!DOCTYPE html>
<html lang="en">

<head>
    @include('admin.includes.header')
    @include('admin.includes.css')
</head>

<body>

    <!-- START Wrapper -->
    <div class="wrapper">

        <!-- ========== Topbar Start ========== -->
        @include('admin.includes.topbar')

        <!-- ========== App Menu Start ========== -->
        @include('admin.includes.sidebar')
        <!-- ========== App Menu End ========== -->

        <!-- ==================================================== -->
        <!-- Start right Content here -->
        <!-- ==================================================== -->
        <div class="page-content" >

            <!-- Start Container Fluid -->
            <div class="container-fluid">
                <!-- Start here.... -->
                <!-- ========== Page Title Start ========== -->
                @include('admin.includes.breadcrumb')
                <!-- ========== Page Title End ========== -->
                @yield('content')
            </div>
            <!-- End Container Fluid -->

            <!-- ========== Footer Start ========== -->
            @include('admin.includes.footer')
            <!-- ========== Footer End ========== -->

        </div>
        <!-- ==================================================== -->
        <!-- End Page Content -->
        <!-- ==================================================== -->

    </div>
    <!-- END Wrapper -->

    @include('admin.includes.scripts')
</body>

</html>
