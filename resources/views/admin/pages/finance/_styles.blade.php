<style>
/* ── Finance module shared styles ────────────────────────────────── */

/* Status badges */
.finance-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}
.badge-finance-paid    { background: #d1fae5; color: #065f46; }
.badge-finance-partial { background: #dbeafe; color: #1e40af; }
.badge-finance-pending { background: #fef9c3; color: #713f12; }
.badge-finance-overdue { background: #fee2e2; color: #991b1b; }
.badge-finance-waived  { background: #f3f4f6; color: #4b5563; }

/* Quick-stat mini cards */
.finance-stat-card {
    border-radius: 12px;
    padding: 18px 20px;
    position: relative;
    overflow: hidden;
    transition: transform .15s ease;
}
.finance-stat-card:hover { transform: translateY(-2px); }
.finance-stat-card .stat-icon {
    width: 52px; height: 52px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}
.finance-stat-card .stat-value { font-size: 22px; font-weight: 700; line-height: 1.2; }
.finance-stat-card .stat-label { font-size: 12px; font-weight: 500; opacity: .75; margin-bottom: 4px; }
.finance-stat-card .stat-sub   { font-size: 12px; opacity: .65; }

/* Payment method badges */
.method-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 9px; border-radius: 20px; font-size: 11px; font-weight: 600;
}
.method-cash     { background:#dcfce7; color:#166534; }
.method-bkash    { background:#fce7f3; color:#9d174d; }
.method-nagad    { background:#ffedd5; color:#9a3412; }
.method-bank_transfer { background:#ede9fe; color:#5b21b6; }
.method-other    { background:#f3f4f6; color:#374151; }

/* Finance filter card */
.finance-filter-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 16px;
}

/* Progress bar for dues */
.due-progress { height: 5px; border-radius: 4px; }

/* Quick action buttons */
.finance-quick-action {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; gap: 6px;
    padding: 14px 12px; border-radius: 10px;
    text-decoration: none; font-size: 12px; font-weight: 600;
    transition: all .15s ease; border: 2px solid transparent;
    cursor: pointer; background: none;
}
.finance-quick-action i { font-size: 22px; }
.finance-quick-action:hover { transform: translateY(-2px); }

/* Table row hover with action reveal */
.finance-table tbody tr:hover .row-actions { opacity: 1; }
.row-actions { opacity: .6; transition: opacity .15s; }

/* Responsive: card view on mobile */
@media (max-width: 767.98px) {
    .finance-table thead { display: none; }
    .finance-table tbody tr {
        display: block; margin-bottom: 12px;
        border: 1px solid #e2e8f0; border-radius: 10px;
        padding: 12px 14px; background: #fff;
    }
    .finance-table tbody td {
        display: flex; justify-content: space-between; align-items: center;
        padding: 5px 0; border: none; font-size: 13px;
    }
    .finance-table tbody td::before {
        content: attr(data-label);
        font-weight: 600; color: #6b7280; font-size: 11px;
        text-transform: uppercase; letter-spacing: .5px;
        flex-shrink: 0; margin-right: 10px;
    }
}
</style>
