@extends('admin.layouts.theme')

@section('content')
    <div class="row">
        <div class="col-3">
            <div class="card custom-card-border">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-6">
                            <div class="avatar-md bg-light bg-opacity-50 rounded">
                                <iconify-icon icon="solar:buildings-2-broken"
                                    class="fs-32 text-primary avatar-title"></iconify-icon>
                            </div>
                            <p class="text-muted mb-2 mt-3">Total Donations</p>
                            <h3 class="text-dark fw-bold d-flex align-items-center gap-2 mb-0">
                                <span id="total_donationsTxt">0</span>
                                <span class="badge text-success bg-success-subtle fs-12 summary-container">
                                    <i class="ri-arrow-up-line"></i>
                                    <span id="total_donationsPercentage">0%</span>
                                </span>
                            </h3>
                        </div>
                        <div class="col-6">
                            <div id="total_donations" class="apex-charts" style="min-height: 95px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="card custom-card-border">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-6">
                            <div class="avatar-md bg-light bg-opacity-50 rounded">
                                <iconify-icon icon="solar:buildings-2-broken"
                                    class="fs-32 text-primary avatar-title"></iconify-icon>
                            </div>
                            <p class="text-muted mb-2 mt-3">Tournament Fee's</p>
                            <h3 class="text-dark fw-bold d-flex align-items-center gap-2 mb-0">
                                <span id="total_tournamentFeesTxt"></span>
                                <span class="badge text-success bg-success-subtle fs-12 summary-container">
                                    <i class="ri-arrow-up-line"></i>
                                    <span id="total_tournamentFeesPercentage">0%</span>
                                </span>
                            </h3>
                        </div>
                        <div class="col-6">
                            <div id="total_tournamentFees" class="apex-charts" style="min-height: 95px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="card custom-card-border">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-6">
                            <div class="avatar-md bg-light bg-opacity-50 rounded">
                                <iconify-icon icon="solar:users-broken"
                                    class="fs-32 text-primary avatar-title"></iconify-icon>
                            </div>
                            <p class="text-muted mb-2 mt-3">Player Payment Status</p>
                            <h3 class="text-dark fw-bold d-flex flex-row gap-1 mb-0">
                                <span id="playersPaidTxt">0</span>
                                <span id="" class="ml-2 mr-2">/</span>
                                <span id="playersNotPaidTxt">0</span>
                            </h3>
                        </div>
                        <div class="col-6">
                            <div id="players_payment_chart" class="apex-charts" style="min-height: 95px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-3">
            <div class="card custom-card-border">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-6">
                            <div class="avatar-md bg-light bg-opacity-50 rounded">
                                <iconify-icon icon="solar:buildings-2-broken"
                                    class="fs-32 text-primary avatar-title"></iconify-icon>
                            </div>
                            <p class="text-muted mb-2 mt-3">Other Fee's</p>
                            <h3 class="text-dark fw-bold d-flex align-items-center gap-2 mb-0">
                                <span id="total_otherFeesTxt"></span>
                                <span class="badge text-success bg-success-subtle fs-12 summary-container">
                                    <i class="ri-arrow-up-line"></i>
                                    <span id="total_otherFeesPercentage">0%</span>
                                </span>
                            </h3>
                        </div>
                        <div class="col-6">
                            <div id="total_otherFees" class="apex-charts" style="min-height: 95px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="card custom-card-border">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">Payments Summary</h5>
                    <div class="d-flex align-items-center gap-2">
                        <select id="filter-month" class="form-select form-select-sm border-cyan">
                            <option value="">All Months</option>
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endforeach
                        </select>

                        @if (Auth::user()->can('payments-create'))
                            <button type="button" class="btn btn-primary text-nowrap" data-bs-toggle="modal"
                                data-bs-target="#add-payment">
                                Add New
                            </button>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12 table-responsive">
                            <table class="table datatable no-footer" id="tbl-payments">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Player Name</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Payment Date</th>
                                        <th>Tournament</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot></tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Payment Modal --}}
    <div class="modal fade" id="add-payment" tabindex="-1" aria-labelledby="add-paymentTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.payments.store') }}" method="post" id="form-payment-add">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="player_id" class="form-label">Player</label>
                            <select name="player_id" id="player_id" class="form-control" required>
                                <option value="">Select Player</option>
                                @foreach (\App\Models\Player::all() as $player)
                                    <option value="{{ $player->id }}">{{ $player->user->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">Payment Type</label>
                            <select name="type" id="type" class="form-control" required>
                                <option value="donation">Donation</option>
                                <option value="tournament">Tournament</option>
                                <option value="jersey">Jersey</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="number" name="amount" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" class="form-control" required>
                                <option value="paid">Paid</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="payment_date" class="form-label">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Payment Modal --}}
    <div class="modal fade" id="edit-payment" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Payment</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" id="form-payment-edit">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Player</label>
                            <select name="player_id" id="edit_player_id" class="form-control" required>
                                @foreach (\App\Models\Player::all() as $player)
                                    <option value="{{ $player->id }}">{{ $player->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Type</label>
                            <select name="type" id="edit_type" class="form-control" required>
                                <option value="one-time">One-Time</option>
                                <option value="recurring">Recurring</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" name="amount" id="edit_amount" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit_status" class="form-control" required>
                                <option value="paid">Paid</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Date</label>
                            <input type="date" name="payment_date" id="edit_payment_date" class="form-control"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            function capitalize(str) {
                if (!str) return '';
                return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
            }
            let table = $('#tbl-payments').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('admin.payments.loader') }}',
                    error: function(data) {
                        console.error(data);
                    },
                    // success: function(data) {
                    //     console.info(data);
                    // },
                },
                order: [
                    [1, 'asc']
                ],
                columns: [{
                        data: 'player_id',
                        name: 'players.id'
                    },
                    {
                        data: 'player_name',
                        name: 'player_name'
                    },
                    {
                        data: 'type',
                        name: 'payments.type',
                        render: function(data, type, row) {
                            if (!data) {
                                return `-`;
                            }

                            let badgeClass = 'bg-secondary';
                            switch (data) {
                                case 'donation':
                                    badgeClass = 'bg-info';
                                    break;
                                case 'tournament':
                                    badgeClass = 'bg-warning text-dark';
                                    break;
                                case 'jersey':
                                    badgeClass = 'bg-info';
                                    break;
                                case 'other':
                                    badgeClass = 'bg-secondary';
                                    break;
                            }
                            return `<span class="badge fs-16 ${badgeClass}">${capitalize(data)}</span>`;
                        }
                    },
                    {
                        data: 'amount',
                        name: 'payments.amount'
                    },
                    {
                        data: 'status',
                        name: 'payments.status',
                        render: function(data, type, row) {
                            if (!data) {
                                return `<span class="badge fs-16 bg-danger">Pending</span>`;
                            }

                            let badgeClass = data === 'paid' ? 'bg-success' : 'bg-danger';
                            return `<span class="badge fs-16 ${badgeClass}">${capitalize(data)}</span>`;
                        }
                    },
                    {
                        data: 'payment_date',
                        name: 'payments.payment_date'
                    },
                    {
                        data: 'tournament_name',
                        name: 'tournaments.name'
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                footerCallback: function(row, data, start, end, display) {
                    let api = this.api();
                    let totalDonations = 0,
                        totalTournament = 0,
                        totalRegistration = 0,
                        totalOther = 0;

                    data.forEach(function(payment) {
                        let amount = parseFloat(payment.amount) || 0;
                        switch (payment.type) {
                            case 'donation':
                                totalDonations += amount;
                                break;
                            case 'tournament':
                                totalTournament += amount;
                                break;
                            case 'jersey':
                                totalRegistration += amount;
                                break; // assuming jersey = registration
                            case 'other':
                                totalOther += amount;
                                break;
                        }
                    });

                    $(api.column(3).footer()).html(
                        `Donations: ${totalDonations} | Tournament: ${totalTournament} | Registration: ${totalRegistration} | Other: ${totalOther}`
                    );
                },
                initComplete: function(settings, json) {
                    updateSummaryCards(json.summary, json.percentages, json.playerStatus);
                    renderDayWiseCharts(json.dayWise);
                }
            });

            function updateSummaryCards(summary, percentages, playerStatus) {
                const updateCard = (txtId, percId, value, perc) => {
                    const $txt = $(`#${txtId}`);
                    const $perc = $(`#${percId}`);
                    const $badge = $perc.closest('.summary-container');
                    const $icon = $badge.find('i');

                    $txt.text(value);
                    $perc.text(Math.abs(perc) + '%');

                    if (perc > 0) {
                        $badge.removeClass('text-danger bg-danger-subtle')
                            .addClass('text-success bg-success-subtle');
                        $icon.removeClass('ri-arrow-down-line').addClass('ri-arrow-up-line');
                    } else if (perc < 0) {
                        $badge.removeClass('text-success bg-success-subtle')
                            .addClass('text-danger bg-danger-subtle');
                        $icon.removeClass('ri-arrow-up-line').addClass('ri-arrow-down-line');
                    } else {
                        $badge.removeClass('text-success bg-success-subtle text-danger bg-danger-subtle');
                        $icon.removeClass('ri-arrow-up-line ri-arrow-down-line');
                    }
                };

                updateCard('total_donationsTxt', 'total_donationsPercentage', summary.donation, percentages
                    .donation);
                updateCard('total_tournamentFeesTxt', 'total_tournamentFeesPercentage', summary.tournament,
                    percentages.tournament);
                updateCard('total_otherFeesTxt', 'total_otherFeesPercentage', summary.other, percentages.other);

                if (playerStatus) {
                    $('#playersPaidTxt').text(playerStatus.paid);
                    $('#playersNotPaidTxt').text(playerStatus.notPaid);

                    // Render small Apex donut chart
                    var chart = new ApexCharts(document.querySelector("#players_payment_chart"), {
                        chart: {
                            type: 'donut',
                            height: 95
                        },
                        series: [playerStatus.paid, playerStatus.notPaid],
                        labels: ['Paid', 'Not Paid'],
                        colors: ['#198754', '#dc3545'],
                        legend: {
                            show: false
                        },
                        dataLabels: {
                            enabled: false
                        },
                        tooltip: {
                            enabled: true
                        }
                    });
                    chart.render();
                }
            }

            function renderDayWiseCharts(dayWise) {
                const chartOptions = (element, data, color) => ({
                    chart: {
                        height: 95,
                        type: 'bar',
                        parentHeightOffset: 0,
                        toolbar: {
                            show: false
                        },
                    },
                    plotOptions: {
                        bar: {
                            barHeight: "100%",
                            columnWidth: "40%",
                            startingShape: "rounded",
                            endingShape: "rounded",
                            borderRadius: 4,
                            distributed: true
                        }
                    },
                    grid: {
                        show: false,
                        padding: {
                            top: -20,
                            bottom: -10,
                            left: 0,
                            right: 0
                        },
                    },
                    colors: Array.isArray(data) ? data.map((val) => val > 0 ? color : '#eef2f7') : [],
                    dataLabels: {
                        enabled: false
                    },
                    series: [{
                        name: element,
                        data: Array.isArray(data) ? data : []
                    }],
                    legend: {
                        show: false
                    },
                    xaxis: {
                        categories: Array.isArray(data) ? data.map((_, idx) => idx + 1) : [],
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        },
                    },
                    yaxis: {
                        labels: {
                            show: false
                        }
                    },
                    tooltip: {
                        enabled: true
                    },
                    responsive: [{
                        breakpoint: 1025,
                        options: {
                            chart: {
                                height: 199
                            }
                        }
                    }],
                });
                console.log(dayWise.donation)
                if (dayWise.donation) {
                    const c1 = new ApexCharts(document.querySelector("#total_donations"), chartOptions('Donations',
                        dayWise.donations, '#0d6efd')).render();
                }
                if (dayWise.tournament) {
                    new ApexCharts(document.querySelector("#total_tournamentFees"), chartOptions('Tournament',
                        dayWise.tournament, '#ffc107')).render();
                }
                if (dayWise.registration) {
                    new ApexCharts(document.querySelector("#total_registrationFees"), chartOptions('Registration',
                        dayWise.registration, '#198754')).render();
                }
                if (dayWise.other) {
                    new ApexCharts(document.querySelector("#total_otherFees"), chartOptions('Other', dayWise.other,
                        '#dc3545')).render();
                }
            }


            $('#filter-month').on('change', function() {
                let month = $(this).val();
                let table = $('#tbl-payments').DataTable();
                table.ajax.url('/admin/payments/loader?month=' + month).load();
            });

            // Add Payment via AJAX
            $('#form-payment-add').on('submit', function(e) {
                e.preventDefault();

                let $form = $(this);
                let $submitBtn = $form.find('button[type="submit"]');
                $submitBtn.prop('disabled', true);

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                    success: function(res) {
                        $('#add-payment').modal('hide');
                        $form[0].reset();
                        table.ajax.reload();
                        Swal.fire('Success', res.message, 'success');
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) { // Validation error
                            let errors = xhr.responseJSON.errors;
                            let errorMessages = '';
                            $.each(errors, function(key, msgs) {
                                errorMessages += msgs.join('<br>') + '<br>';
                            });
                            Swal.fire('Validation Error', errorMessages, 'error');
                        } else {
                            Swal.fire('Error', 'Something went wrong.', 'error');
                        }
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false);
                    }
                });
            });

            // Edit Payment Modal
            $(document).on('click', '.edit-payment-btn', function() {
                let data = $(this).data();
                $('#edit-payment').modal('show');
                $('#form-payment-edit').attr('action', '/admin/payments/' + data.id);
                $('#edit_player_id').val(data.player);
                $('#edit_type').val(data.type);
                $('#edit_amount').val(data.amount);
                $('#edit_status').val(data.status);
                $('#edit_payment_date').val(data.date);
            });

            // Update Payment via AJAX
            $('#form-payment-edit').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'PUT',
                    data: $(this).serialize(),
                    success: function(res) {
                        $('#edit-payment').modal('hide');
                        table.ajax.reload();
                        Swal.fire('Success', 'Payment updated successfully!', 'success');
                    },
                    error: function(err) {
                        Swal.fire('Error', 'Something went wrong.', 'error');
                    }
                });
            });

            // Delete Payment
            $(document).on('click', '.delete-payment-btn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This payment will be deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/admin/payments/' + id,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                table.ajax.reload();
                                Swal.fire('Deleted!', 'Payment has been deleted.',
                                    'success');
                            },
                            error: function(err) {
                                Swal.fire('Error!', 'Something went wrong.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
