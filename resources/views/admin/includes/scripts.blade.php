<!-- Vendor Javascript (Require in all Page) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="{{asset('storage/assets/js/vendor.js') }}"></script>

<!-- App Javascript (Require in all Page) -->
<script src="{{asset('storage/assets/js/app.js') }}"></script>
<script src="{{asset('storage/assets/vendor/jsvectormap/js/jsvectormap.min.js') }}"></script>
<script src="{{asset('storage/assets/vendor/jsvectormap/maps/world-merc.js') }}"></script>
<script src="{{asset('storage/assets/vendor/jsvectormap/maps/world.js') }}"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
@if (Route::is('admin.dashboard'))
    <script src="{{ asset('storage/assets/js/pages/dashboard.js') }}"></script>
@endif
@stack('scripts')
