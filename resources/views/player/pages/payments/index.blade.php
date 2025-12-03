@extends('player.layouts.theme')

@section('content')
    @push('styles')
        <style>
            /* Table modern style */
            .table-modern {
                border-radius: 12px;
                overflow: hidden;
                border-collapse: separate;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            }

            .table-modern thead th {
                background: linear-gradient(180deg, #2b0000, #500606);
                color: #fff;
                font-weight: 600;
                border: none;
                text-transform: uppercase;
            }

            .table-modern tbody tr {
                transition: all 0.2s ease;
            }

            .table-modern tbody tr:hover {
                background-color: rgba(0, 123, 255, 0.05);
                transform: scale(1.02);
            }

            .table-modern tbody td {
                border-top: 1px solid #eaeaea;
            }

            .table-modern tfoot tr.table-total {
                background-color: #f8f9fa;
                font-weight: 700;
                font-size: 1rem;
            }

            .table-modern tfoot th {
                border-top: 2px solid #ddd;
            }

            /* Responsive scroll shadow */
            .table-responsive {
                overflow-x: auto;
            }
        </style>
    @endpush

    @php
        $typesList = ['donation', 'tournament', 'jersey', 'other', 'monthly'];
    @endphp

    <div class="row">
        @foreach ($typesList as $type)
            <div class="col-2">
                <div class="card custom-card-border">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-12">
                                <div class="avatar-md bg-light bg-opacity-50 rounded">
                                    <iconify-icon icon="solar:coins-broken"
                                        class="fs-32 text-primary avatar-title"></iconify-icon>
                                </div>
                                <p class="text-muted mb-2 mt-3">{{ ucfirst($type) }} Fees</p>
                                <h3 class="text-dark fw-bold d-flex align-items-center gap-2 mb-0">
                                    <span
                                        id="total_{{ $type }}Txt">{{ number_format($yearSummary[$type] ?? 0, 2) }}</span>
                                    <span class="badge text-success bg-success-subtle fs-12 summary-container">
                                        <i class="ri-arrow-up-line"></i>
                                        <span id="total_{{ $type }}Percentage">0%</span>
                                    </span>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="col-2">
            <div class="card custom-card-border">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-12">
                            <div class="avatar-md bg-light bg-opacity-50 rounded">
                                <iconify-icon icon="solar:coins-broken"
                                    class="fs-32 text-primary avatar-title"></iconify-icon>
                            </div>
                            <p class="text-muted mb-2 mt-3">Total Fees</p>
                            <h3 class="text-dark fw-bold d-flex align-items-center gap-2 mb-0">
                                <span
                                    id="total_{{ $type }}Txt">{{ number_format($yearSummary['total'] ?? 0, 2) }}</span>
                                <span class="badge text-success bg-success-subtle fs-12 summary-container">
                                    <i class="ri-arrow-up-line"></i>
                                    <span id="total_{{ $type }}Percentage">0%</span>
                                </span>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-sm-12">
            <div class="card custom-card-border">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12 table-responsive">
                            <table class="table table-modern table-hover align-middle" id="tbl-payments">
                                <thead class="table-dark">
                                    <tr>
                                        <th>MONTH</th>
                                        @foreach ($typesList as $type)
                                            <th class="text-center">{{ strtoupper($type) }}</th>
                                        @endforeach
                                        <th class="text-center">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($monthWise as $monthName => $monthData)
                                        <tr>
                                            <td class="fw-bold bg-light">{{ $monthName }}</td>
                                            @foreach ($typesList as $type)
                                                <td class="text-center">{{ number_format($monthData[$type] ?? 0, 2) }}</td>
                                            @endforeach
                                            <td class="text-center fw-bold">
                                                {{ number_format($monthData['total'] ?? 0, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-total">
                                        <th class="text-end fs-6 fw-bold">Total</th>
                                        @foreach ($typesList as $type)
                                            <th class="text-center text-primary">
                                                {{ number_format(array_sum(array_column($monthWise, $type)), 2) }}
                                            </th>
                                        @endforeach
                                        <th class="text-center text-success fs-6 fw-bold">
                                            {{ number_format(array_sum(array_column($monthWise, 'total')), 2) }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

        });
    </script>
@endpush
