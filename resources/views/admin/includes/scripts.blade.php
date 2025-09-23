<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="{{asset('storage/assets/js/vendor.js') }}"></script>

<!-- App Javascript (Require in all Page) -->
<script src="{{asset('storage/assets/js/app.js') }}"></script>
<script src="{{asset('storage/assets/vendor/jsvectormap/js/jsvectormap.min.js') }}"></script>
<script src="{{asset('storage/assets/vendor/jsvectormap/maps/world-merc.js') }}"></script>
<script src="{{asset('storage/assets/vendor/jsvectormap/maps/world.js') }}"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dropify@0.2.2/dist/js/dropify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.js"></script>
@if (Route::is('admin.dashboard'))
    <script src="{{ asset('storage/assets/js/pages/dashboard.js') }}"></script>
@endif

@if(Route::is('admin.cricket-matches.start'))
<script src="{{asset('storage/backend/js/scoreboard.js')}}"></script>
@endif

@stack('scripts')

<script>
    @if(session()->has('message'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: '{{ session('success') ? 'success' : 'error' }}',
            title: '{{ session('message') }}',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    @endif

    document.addEventListener('DOMContentLoaded', function () {
        @if(session('toast_success'))
            Swal.fire({
                icon: 'success',
                title: '{{ session('toast_success') }}',
                position: 'top-right',
                showConfirmButton: false,
                timer: 4000,
                toast: true,
            });
        @endif

        @if(session('toast_error'))
            Swal.fire({
                icon: 'error',
                title: '{{ session('toast_error') }}',
                position: 'top-right',
                showConfirmButton: false,
                timer: 4000,
                toast: true,
            });
        @endif
    });
</script>
