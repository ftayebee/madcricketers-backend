@php
/**
 * Usage: @include('admin.pages.finance._status_badge', ['status' => $due->status, 'isOverdue' => $due->is_overdue])
 * $isOverdue is optional — only applies to pending/partial
 */
$s = $status ?? 'pending';
$overdue = $isOverdue ?? false;

if ($overdue && in_array($s, ['pending', 'partial'])) {
    $cls  = 'badge-finance-overdue';
    $icon = 'ri-alarm-warning-fill';
    $label = 'Overdue';
} elseif ($s === 'paid') {
    $cls  = 'badge-finance-paid';
    $icon = 'ri-checkbox-circle-fill';
    $label = 'Paid';
} elseif ($s === 'partial') {
    $cls  = 'badge-finance-partial';
    $icon = 'ri-contrast-2-line';
    $label = 'Partial';
} elseif ($s === 'waived') {
    $cls  = 'badge-finance-waived';
    $icon = 'ri-scissors-cut-line';
    $label = 'Waived';
} else {
    $cls  = 'badge-finance-pending';
    $icon = 'ri-time-line';
    $label = 'Pending';
}
@endphp
<span class="finance-status-badge {{ $cls }}">
    <i class="{{ $icon }}"></i> {{ $label }}
</span>
