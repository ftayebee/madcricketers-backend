@php
    $status = strtolower($status ?? 'pending');
    $classes = [
        'paid' => 'bg-success',
        'partial' => 'bg-warning text-dark',
        'pending' => 'bg-warning text-dark',
        'overdue' => 'bg-danger',
        'waived' => 'bg-secondary',
    ];
@endphp
<span class="badge {{ $classes[$status] ?? 'bg-info' }}">{{ ucfirst($status) }}</span>
