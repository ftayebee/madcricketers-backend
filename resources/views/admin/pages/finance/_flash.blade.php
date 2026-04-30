@if(session('success') === true)
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
    <div class="d-flex align-items-center gap-2">
        <i class="ri-checkbox-circle-fill fs-18 text-success"></i>
        <span class="fw-medium">{{ session('message') }}</span>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@elseif(session('success') === false)
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
    <div class="d-flex align-items-center gap-2">
        <i class="ri-error-warning-fill fs-18 text-danger"></i>
        <span class="fw-medium">{{ session('message') }}</span>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
    <div class="d-flex align-items-start gap-2">
        <i class="ri-error-warning-fill fs-18 text-danger mt-1 flex-shrink-0"></i>
        <div>
            <div class="fw-medium mb-1">Please fix the following errors:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
