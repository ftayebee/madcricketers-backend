
<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="dark" data-bs-theme="light">

    <head>
        
        @include('admin.includes.header')
        @include('admin.includes.css')
    </head>

    
    <!-- Top Bar Start -->
    <body>
        <!-- Top Bar Start -->
        @include('admin.includes.topbar')
        <!-- Top Bar End -->

        <!-- leftbar-tab-menu -->
        @include('admin.includes.sidebar')
        <div class="startbar-overlay d-print-none"></div>
        <!-- end leftbar-tab-menu-->


        <div class="page-wrapper">
            <!-- Page Content-->
            <div class="page-content">
                <div class="container-fluid">
                    @include('admin.includes.breadcrumb')
                    
                    @yield('content')
                </div><!-- container -->

                <!--Start Footer-->
                @include('admin.includes.footer')
                <!--end footer-->
            </div>
            <!-- end page content -->
        </div>
        <!-- end page-wrapper -->

        @include('admin.includes.scripts')
    </body>
    <!--end body-->
</html>