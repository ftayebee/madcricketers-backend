@extends('admin.layouts.theme')

@push('styles')
    @include('admin.pages.finance.partials.styles')
@endpush

@section('content')
<div class="finance-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h3 class="mb-1">Finance Dashboard</h3>
            <p class="text-muted mb-0">Collection, dues, and balance snapshot for club admins.</p>
        </div>
        <div class="finance-action">
            @can('finance-dues-manage')
                <a href="{{ route('admin.finance.dues.index') }}" class="btn btn-primary"><i class="ri-add-line me-1"></i>Add Due</a>
            @endcan
            @can('finance-payments-manage')
                <a href="{{ route('admin.finance.payments.create') }}" class="btn btn-success"><i class="ri-hand-coin-line me-1"></i>Receive Payment</a>
            @endcan
            @can('finance-expenses-manage')
                <a href="{{ route('admin.finance.expenses.index') }}" class="btn btn-danger"><i class="ri-wallet-3-line me-1"></i>Add Expense</a>
            @endcan
            @can('finance-reports-view')
                <a href="{{ route('admin.finance.reports.index') }}" class="btn btn-info"><i class="ri-bar-chart-2-line me-1"></i>Reports</a>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6 col-xl-3">@include('admin.pages.finance.partials.summary-card', ['label' => 'Total Received', 'value' => number_format($dashboard['total_received'], 2), 'color' => 'success', 'icon' => 'ri-arrow-down-circle-line'])</div>
        <div class="col-md-6 col-xl-3">@include('admin.pages.finance.partials.summary-card', ['label' => 'Total Due', 'value' => number_format($dashboard['total_due'], 2), 'color' => 'warning', 'icon' => 'ri-calendar-todo-line'])</div>
        <div class="col-md-6 col-xl-3">@include('admin.pages.finance.partials.summary-card', ['label' => 'Total Expense', 'value' => number_format($dashboard['total_expense'], 2), 'color' => 'danger', 'icon' => 'ri-arrow-up-circle-line'])</div>
        <div class="col-md-6 col-xl-3">@include('admin.pages.finance.partials.summary-card', ['label' => 'Current Balance', 'value' => number_format($dashboard['current_balance'], 2), 'color' => 'primary', 'icon' => 'ri-bank-card-line'])</div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">@include('admin.pages.finance.partials.summary-card', ['label' => 'Monthly Received', 'value' => number_format($dashboard['monthly_received'], 2), 'color' => 'success', 'icon' => 'ri-funds-line'])</div>
        <div class="col-md-4">@include('admin.pages.finance.partials.summary-card', ['label' => 'Monthly Expenses', 'value' => number_format($dashboard['monthly_expenses'], 2), 'color' => 'danger', 'icon' => 'ri-receipt-line'])</div>
        <div class="col-md-4">@include('admin.pages.finance.partials.summary-card', ['label' => 'Pending Dues', 'value' => $dashboard['pending_dues'], 'hint' => $dashboard['overdue_players'].' players have overdue dues', 'color' => 'warning', 'icon' => 'ri-alert-line'])</div>
    </div>

    <div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span><strong>{{ $dashboard['overdue_players'] }}</strong> players have overdue dues. <strong>{{ $dashboard['pending_dues'] }}</strong> dues are pending this month.</span>
        <span>Monthly donation collection progress: <strong>{{ number_format($dashboard['monthly_received'], 2) }}</strong></span>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0">Current Month Donation Due</h5>
            <span class="badge bg-primary">{{ $currentMonthDonation['period_label'] }}</span>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <div class="small text-muted">Players</div>
                    <div class="fw-bold">{{ $currentMonthDonation['paid_players'] }} paid / {{ $currentMonthDonation['unpaid_players'] }} unpaid</div>
                    <div class="small text-muted">Total active: {{ $currentMonthDonation['total_active_players'] }}</div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">Expected</div>
                    <h5 class="mb-0">{{ number_format($currentMonthDonation['total_expected'], 2) }}</h5>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">Collected</div>
                    <h5 class="mb-0 text-success">{{ number_format($currentMonthDonation['total_collected'], 2) }}</h5>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">Remaining</div>
                    <h5 class="mb-2 text-danger">{{ number_format($currentMonthDonation['remaining_amount'], 2) }}</h5>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ min(100, $currentMonthDonation['collection_percentage']) }}%"></div>
                    </div>
                    <div class="small text-muted mt-1">{{ $currentMonthDonation['collection_percentage'] }}% collected</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white"><h5 class="mb-0">Income vs Expense</h5></div>
                <div class="card-body"><div id="finance-income-expense-chart"></div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white"><h5 class="mb-0">Top Defaulters</h5></div>
                <div class="card-body">
                    @forelse($topDefaulters as $row)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong>{{ $row['player']?->user?->full_name ?? 'Unknown player' }}</strong>
                                <div class="small text-muted">{{ $row['due_count'] }} open dues</div>
                            </div>
                            <span class="badge bg-danger">{{ number_format($row['remaining'], 2) }}</span>
                        </div>
                    @empty
                        @include('admin.pages.finance.partials.empty-state', ['title' => 'No defaulters', 'message' => 'Everyone is clear right now.', 'icon' => 'ri-shield-check-line'])
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white"><h5 class="mb-0">Recent Payments</h5></div>
                <div class="card-body">
                    @if($recentPayments->count())
                        <div class="table-responsive">
                            <table class="table finance-table">
                                <thead><tr><th>Player</th><th>Category</th><th>Amount</th><th>Date</th></tr></thead>
                                <tbody>
                                    @foreach($recentPayments as $payment)
                                        <tr>
                                            <td data-label="Player">{{ $payment->player?->user?->full_name ?? '-' }}</td>
                                            <td data-label="Category">{{ ucfirst($payment->type) }}</td>
                                            <td data-label="Amount" class="text-success fw-bold">{{ number_format($payment->amount, 2) }}</td>
                                            <td data-label="Date">{{ optional($payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date) : null)->format('d M Y') ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        @include('admin.pages.finance.partials.empty-state')
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white"><h5 class="mb-0">Recent Expenses</h5></div>
                <div class="card-body">@include('admin.pages.finance.partials.empty-state', ['title' => 'No expenses recorded', 'message' => 'Expense storage is not configured in this checkout.', 'icon' => 'ri-receipt-line'])</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    new ApexCharts(document.querySelector('#finance-income-expense-chart'), {
        chart: { type: 'area', height: 320, toolbar: { show: false } },
        series: [
            { name: 'Income', data: @json($monthlyIncome) },
            { name: 'Expense', data: @json($monthlyExpense) }
        ],
        colors: ['#198754', '#dc3545'],
        xaxis: { categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 }
    }).render();
</script>
@endpush
