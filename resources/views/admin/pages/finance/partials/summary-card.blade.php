<div class="card finance-stat h-100">
    <div class="card-body d-flex justify-content-between gap-3">
        <div>
            <p class="text-muted mb-2">{{ $label }}</p>
            <h3 class="mb-1 fw-bold">{{ $value }}</h3>
            @isset($hint)
                <span class="small text-muted">{{ $hint }}</span>
            @endisset
        </div>
        <div class="finance-icon bg-{{ $color ?? 'primary' }}-subtle text-{{ $color ?? 'primary' }}">
            <i class="{{ $icon ?? 'ri-money-dollar-circle-line' }}"></i>
        </div>
    </div>
</div>
