@extends('admin.layouts.theme')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-4 align-items-center">
                        {{-- Team Logo --}}
                        <div class="col-md-2 text-center">
                            <div class="rounded border p-2 bg-light">
                                @if ($team->logo)
                                    <img src="{{ $team->logo }}" class="img-fluid rounded" style="max-height: 100px;"
                                        alt="{{ $team->name }}">
                                @else
                                    <div class="text-muted small">No Logo</div>
                                @endif
                            </div>
                        </div>

                        {{-- Team Basic Info --}}
                        <div class="col-md-8">
                            <h4 class="fw-bold">{{ $team->name }}</h4>
                            <p class="mb-1"><strong>Coach:</strong> {{ $team->coach_name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Manager:</strong> {{ $team->manager_name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Players:</strong> {{ $team->players->count() ?? 'N/A' }}</p>
                            <p class="text-muted">{{ $team->description ?? 'No description provided.' }}</p>
                        </div>

                        <div class="col-md-2 mt-0" style="text-align: right;">
                            <button class="btn btn-sm btn-info" data-bs-target="#modal-assign-player"
                                data-bs-toggle="modal">Add New Player</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @php
            $groupedPlayers = $team->players->groupBY('player_role');
        @endphp
        @foreach ($groupedPlayers as $role => $players)
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ ucfirst($role) }}s ({{ count($players) }})</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row">
                            @php
                                $total = $players->count();
                                $perRow = 3;
                                $lastRowStart = $total - ($total % $perRow === 0 ? $perRow : $total % $perRow) + 1;
                            @endphp
                            @foreach ($players as $player)
                                @php
                                    $isLastRow = $loop->iteration >= $lastRowStart;
                                @endphp
                                <div class="col-md-4 {{ $isLastRow ? 'm-0' : 'mb-3' }}">
                                    <div class="card border shadow-sm h-100 {{ $isLastRow ? 'm-0' : '' }}">
                                        <div class="card-body d-flex align-items-center">
                                            <img src="{{ $player->user->image }}" class="rounded-circle me-3"
                                                width="60" height="60" alt="{{ $player->user->full_name }}">
                                            <div>
                                                <h6 class="mb-1">{{ $player->user->full_name }}</h6>
                                                <small class="text-muted">{{ ucfirst($player->player_role) }}</small>
                                                {{-- <br>
                                                <a href="#"
                                                    class="btn btn-sm btn-outline-primary mt-2">
                                                    View Profile
                                                </a> --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="modal fade" id="modal-assign-player" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add New Player To Team</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form action="" id="form-role-update">
                    @csrf
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Player Details</label>
                                    <input type="text" class="form-control" name="name" id="roleName">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

        });
    </script>
@endpush
