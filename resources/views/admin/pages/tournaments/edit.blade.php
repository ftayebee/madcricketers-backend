@extends('admin.layouts.theme')

@section('content')
    @push('styles')
        <style>
            .cs-label-color { color: #002450; }
            .required-mark  { color: red; font-size: 16px; font-weight: 800; }
            .readonly-badge { font-size: 12px; }
            .current-img    { max-height: 80px; border-radius: 6px; border: 1px solid #dee2e6; padding: 4px; }
        </style>
    @endpush

    <div class="row">
        <div class="col-sm-12">
            <div class="card custom-card-border">
                <div class="card-header bg-soft-cyan">
                    <h5 class="text-center fs-24 fw-bold m-0">Edit Tournament — {{ $tournament->name }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tournaments.update', $tournament->id) }}"
                          method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="modal-body">
                            <div class="row g-3">

                                {{-- Name --}}
                                <div class="col-md-6">
                                    <label class="form-label fs-16 cs-label-color">
                                        Tournament Name <span class="required-mark">*</span>
                                    </label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $tournament->name) }}"
                                           placeholder="Enter tournament name" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Location --}}
                                <div class="col-md-6">
                                    <label class="form-label fs-16 cs-label-color">Location</label>
                                    <input type="text" name="location" class="form-control"
                                           value="{{ old('location', $tournament->location) }}"
                                           placeholder="Enter venue or city">
                                </div>

                                {{-- Start Date --}}
                                <div class="col-md-3">
                                    <label class="form-label fs-16 cs-label-color">
                                        Start Date <span class="required-mark">*</span>
                                    </label>
                                    <input type="date" name="start_date" class="form-control"
                                           value="{{ old('start_date', $tournament->start_date ? \Carbon\Carbon::parse($tournament->start_date)->format('Y-m-d') : '') }}">
                                </div>

                                {{-- End Date --}}
                                <div class="col-md-3">
                                    <label class="form-label fs-16 cs-label-color">
                                        End Date <span class="required-mark">*</span>
                                    </label>
                                    <input type="date" name="end_date" class="form-control"
                                           value="{{ old('end_date', $tournament->end_date ? \Carbon\Carbon::parse($tournament->end_date)->format('Y-m-d') : '') }}">
                                </div>

                                {{-- Status --}}
                                <div class="col-md-3">
                                    <label class="form-label fs-16 cs-label-color">
                                        Status <span class="required-mark">*</span>
                                    </label>
                                    <select name="status" class="form-select">
                                        <option value="upcoming"  {{ old('status', $tournament->status) === 'upcoming'  ? 'selected' : '' }}>Upcoming</option>
                                        <option value="ongoing"   {{ old('status', $tournament->status) === 'ongoing'   ? 'selected' : '' }}>Ongoing</option>
                                        <option value="completed" {{ old('status', $tournament->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </div>

                                {{-- Overs Per Innings --}}
                                <div class="col-md-3">
                                    <label class="form-label fs-16 cs-label-color">
                                        Overs Per Innings <span class="required-mark">*</span>
                                    </label>
                                    <input type="number" name="overs_per_innings" class="form-control"
                                           value="{{ old('overs_per_innings', $tournament->overs_per_innings) }}" min="1">
                                </div>

                                {{-- Format (read-only — cannot change post-creation) --}}
                                <div class="col-md-6">
                                    <label class="form-label fs-16 cs-label-color">
                                        Format
                                        <span class="badge bg-secondary readonly-badge ms-1">Read-only</span>
                                    </label>
                                    <input type="text" class="form-control bg-light"
                                           value="{{ ucfirst($tournament->format) }}" readonly>
                                    <small class="text-muted">Format cannot be changed after creation.</small>
                                </div>

                                {{-- Groups (read-only info) --}}
                                @if($tournament->format === 'group' && $tournament->group_count)
                                <div class="col-md-6">
                                    <label class="form-label fs-16 cs-label-color">
                                        Number of Groups
                                        <span class="badge bg-secondary readonly-badge ms-1">Read-only</span>
                                    </label>
                                    <input type="text" class="form-control bg-light"
                                           value="{{ $tournament->group_count }}" readonly>
                                </div>
                                @endif

                                {{-- Trophy Image --}}
                                <div class="col-md-6">
                                    <label class="form-label fs-16 cs-label-color">Trophy Image</label>
                                    @if($tournament->trophy_image)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/uploads/tournaments/' . $tournament->trophy_image) }}"
                                                 class="current-img" alt="Current trophy">
                                            <small class="text-muted ms-2">Current image</small>
                                        </div>
                                    @endif
                                    <input type="file" name="trophy_image" class="form-control" accept="image/*">
                                    <small class="text-muted">Leave blank to keep existing image.</small>
                                </div>

                                {{-- Logo --}}
                                <div class="col-md-6">
                                    <label class="form-label fs-16 cs-label-color">Tournament Logo</label>
                                    @if($tournament->logo)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/uploads/tournaments/' . $tournament->logo) }}"
                                                 class="current-img" alt="Current logo">
                                            <small class="text-muted ms-2">Current logo</small>
                                        </div>
                                    @endif
                                    <input type="file" name="logo" class="form-control" accept="image/*">
                                    <small class="text-muted">Leave blank to keep existing logo.</small>
                                </div>

                                {{-- Description --}}
                                <div class="col-12">
                                    <label class="form-label fs-16 cs-label-color">Description</label>
                                    <textarea name="description" rows="3" class="form-control"
                                              placeholder="Write tournament description...">{{ old('description', $tournament->description) }}</textarea>
                                </div>

                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-3">
                            <a href="{{ route('admin.tournaments.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left me-1"></i> Back to List
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
