@extends('admin.layouts.theme')

@section('content')
    <div class="row mb-3">
        <div class="col-md-3">
            <label for="filter-type" class="form-label">Payment Type</label>
            <select id="filter-type" class="form-select">
                <option value="">All Types</option>
                <option value="donation">Donation</option>
                <option value="tournament">Tournament</option>
                <option value="jersey">Jersey</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="filter-start" class="form-label">Start Date</label>
            <input type="date" id="filter-start" class="form-control">
        </div>
        <div class="col-md-3">
            <label for="filter-end" class="form-label">End Date</label>
            <input type="date" id="filter-end" class="form-control">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button id="btn-filter" class="btn btn-primary w-100">Filter</button>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>Payment Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered" id="tbl-payment-summary">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Total Amount</th>
                                <th>Paid</th>
                                <th>Pending</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            function loadSummary(type = '', start = '', end = '') {
                $.ajax({
                    url: '{{ route('admin.payments.summaryData') }}',
                    data: {
                        type,
                        start,
                        end
                    },
                    success: function(res) {
                        let tbody = '';
                        res.forEach(row => {
                            tbody += `<tr>
                        <td>${row.type}</td>
                        <td>${row.total}</td>
                        <td>${row.paid}</td>
                        <td>${row.pending}</td>
                    </tr>`;
                        });
                        $('#tbl-payment-summary tbody').html(tbody);
                    },
                    error: function(err) {
                        console.error(err);
                    }
                });
            }

            // Initial load
            loadSummary();

            // Filter button click
            $('#btn-filter').on('click', function() {
                let type = $('#filter-type').val();
                let start = $('#filter-start').val();
                let end = $('#filter-end').val();
                loadSummary(type, start, end);
            });
        });
    </script>
@endpush
