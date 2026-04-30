@extends('admin.layouts.theme')

@push('styles')
    @include('admin.pages.finance.partials.styles')
@endpush

@section('content')
<div class="finance-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div><h3 class="mb-1">Finance Summary</h3><p class="text-muted mb-0">Player, category, tournament, and balance reporting.</p></div>
        <button class="btn btn-outline-primary" disabled><i class="ri-download-line me-1"></i>Export</button>
    </div>

    <form method="GET" class="finance-filter mb-3">
        <div class="row g-2">
            <div class="col-md-4"><label class="form-label">Start Date</label><input name="start_date" type="date" class="form-control" value="{{ request('start_date') }}"></div>
            <div class="col-md-4"><label class="form-label">End Date</label><input name="end_date" type="date" class="form-control" value="{{ request('end_date') }}"></div>
            <div class="col-md-4 d-flex align-items-end"><button class="btn btn-primary w-100"><i class="ri-filter-line me-1"></i>Apply Date Range</button></div>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-md-4">@include('admin.pages.finance.partials.summary-card', ['label' => 'Income', 'value' => number_format($totalIncome, 2), 'color' => 'success', 'icon' => 'ri-arrow-down-circle-line'])</div>
        <div class="col-md-4">@include('admin.pages.finance.partials.summary-card', ['label' => 'Expense', 'value' => number_format($totalExpenses, 2), 'color' => 'danger', 'icon' => 'ri-arrow-up-circle-line'])</div>
        <div class="col-md-4">@include('admin.pages.finance.partials.summary-card', ['label' => 'Profit/Loss Balance', 'value' => number_format($totalIncome - $totalExpenses, 2), 'color' => 'primary', 'icon' => 'ri-scales-3-line'])</div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100"><div class="card-header bg-white"><h5 class="mb-0">Player-wise Summary</h5></div><div class="card-body">
                @forelse($playerSummary as $group)
                    <div class="d-flex justify-content-between border-bottom py-2"><span>{{ $group->first()->player?->user?->full_name ?? 'Unknown player' }}</span><strong>{{ number_format($group->where('status', 'paid')->sum('amount'), 2) }}</strong></div>
                @empty @include('admin.pages.finance.partials.empty-state') @endforelse
            </div></div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100"><div class="card-header bg-white"><h5 class="mb-0">Category-wise Summary</h5></div><div class="card-body">
                @forelse($categorySummary as $type => $group)
                    <div class="d-flex justify-content-between border-bottom py-2"><span>{{ ucfirst($type) }}</span><strong>{{ number_format($group->where('status', 'paid')->sum('amount'), 2) }}</strong></div>
                @empty @include('admin.pages.finance.partials.empty-state') @endforelse
            </div></div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100"><div class="card-header bg-white"><h5 class="mb-0">Tournament-wise Summary</h5></div><div class="card-body">
                @forelse($tournamentSummary as $group)
                    <div class="d-flex justify-content-between border-bottom py-2"><span>{{ $group->first()->tournament?->name ?? 'No tournament' }}</span><strong>{{ number_format($group->where('status', 'paid')->sum('amount'), 2) }}</strong></div>
                @empty @include('admin.pages.finance.partials.empty-state', ['title' => 'No tournament collections']) @endforelse
            </div></div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100"><div class="card-header bg-white"><h5 class="mb-0">Income vs Expense Summary</h5></div><div class="card-body">
                <div id="finance-report-chart"></div>
            </div></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    new ApexCharts(document.querySelector('#finance-report-chart'), {
        chart: { type: 'bar', height: 280, toolbar: { show: false } },
        series: [{ name: 'Amount', data: [{{ (float) $totalIncome }}, {{ (float) $totalExpenses }}] }],
        colors: ['#198754', '#dc3545'],
        xaxis: { categories: ['Income', 'Expense'] },
        plotOptions: { bar: { borderRadius: 6, distributed: true } },
        dataLabels: { enabled: false }
    }).render();
</script>
@endpush
