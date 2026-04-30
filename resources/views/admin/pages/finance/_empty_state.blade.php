@php
$icon    = $icon    ?? 'ri-inbox-line';
$title   = $title   ?? 'No data found';
$message = $message ?? 'Nothing here yet.';
$action  = $action  ?? null;   // ['label' => '...', 'modal' => '#...'] or ['label' => '...', 'url' => '...']
@endphp
<div class="text-center py-5 px-3">
    <div class="avatar-xl mx-auto mb-3 bg-light rounded-circle d-flex align-items-center justify-content-center">
        <i class="{{ $icon }} fs-32 text-muted"></i>
    </div>
    <h6 class="fw-semibold text-dark mb-1">{{ $title }}</h6>
    <p class="text-muted fs-14 mb-3">{{ $message }}</p>
    @if($action)
        @if(isset($action['modal']))
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="{{ $action['modal'] }}">
            <i class="ri-add-line me-1"></i> {{ $action['label'] }}
        </button>
        @elseif(isset($action['url']))
        <a href="{{ $action['url'] }}" class="btn btn-sm btn-primary">
            <i class="ri-add-line me-1"></i> {{ $action['label'] }}
        </a>
        @endif
    @endif
</div>
