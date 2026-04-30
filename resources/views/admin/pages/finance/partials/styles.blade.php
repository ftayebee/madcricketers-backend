<style>
    .finance-shell .card { border: 1px solid #e8eef5; border-radius: 8px; box-shadow: 0 8px 22px rgba(15, 23, 42, .04); }
    .finance-stat { min-height: 124px; }
    .finance-icon { width: 46px; height: 46px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 22px; }
    .finance-table th { color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: .02em; white-space: nowrap; }
    .finance-table td { vertical-align: middle; }
    .finance-action { display: flex; flex-wrap: wrap; gap: .5rem; }
    .finance-filter { background: #f8fafc; border: 1px solid #e8eef5; border-radius: 8px; padding: 1rem; }
    .finance-empty { border: 1px dashed #cbd5e1; border-radius: 8px; padding: 2rem; text-align: center; color: #64748b; background: #f8fafc; }
    @media (max-width: 767.98px) {
        .finance-table thead { display: none; }
        .finance-table, .finance-table tbody, .finance-table tr, .finance-table td { display: block; width: 100%; }
        .finance-table tr { border: 1px solid #e8eef5; border-radius: 8px; margin-bottom: .75rem; padding: .5rem; background: #fff; }
        .finance-table td { border: 0; display: flex; justify-content: space-between; gap: 1rem; padding: .45rem .25rem; }
        .finance-table td::before { content: attr(data-label); color: #64748b; font-weight: 600; }
        .finance-action { justify-content: flex-end; }
    }
</style>
