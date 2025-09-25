@extends('admin.layouts.theme')

@section('content')
    
    @push('styles')
        <style>
            .user-profile-card {
                border: 1px solid #eaeaea;
            }

            .approve-btn:hover {
                transform: scale(1.1);
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            }

            .profile-header {
                border-bottom: 1px solid #f0f0f0;
                padding-bottom: 1rem;
            }

            .detail-item {
                background: #f9f9f9;
                padding: 0.75rem;
                border-radius: 5px;
            }

            .additional-info h6 {
                font-size: 0.8rem;
            }

            .avatar-placeholder {
                border: 1px dashed #ddd;
            }
        </style>
    @endpush
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="card shadow custom-card-border">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Player Profile</h4>
                    <div>
                        <a href="{{ route('admin.players.index') }}" class="btn btn-light">← Back to List</a>
                        <a href="{{ route('admin.settings.users.edit', $user->id) }}" class="btn btn-light">Edit</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="user-profile-card bg-white p-4 rounded shadow-sm">
                        <!-- Header Section -->
                        <div class="profile-header d-flex align-items-center mb-4">
                            <div class="d-flex align-items-center justify-content-between">
                                @if ($user->image)
                                    <img src="{{ $user->image }}" alt="{{ $user->full_name }}" class="rounded-circle me-3"
                                        width="80" height="80">
                                @else
                                    <div class="avatar-placeholder rounded-circle me-3 d-flex align-items-center justify-content-center"
                                        style="width: 80px; height: 80px; background: #f0f0f0;">
                                        <i class="fa fa-user fa-2x text-muted"></i>
                                    </div>
                                @endif
                                <div>
                                    <h3 class="mb-1">{{ $user->full_name ?? 'Unnamed Player' }}</h3>
                                    <span
                                        class="badge bg-primary fs-6">{{ strtoupper($user->player->player_type) ?? 'Player' }}</span>
                                    @if ($user->team)
                                        <span class="badge bg-secondary ms-1">{{ $user->team->name }}</span>
                                    @endif
                                    <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'danger' }} fs-6">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ms-auto">
                                <p class="mb-1 text-muted">Mark Player Application As</p>
                                <form action="{{ route('admin.players.approve', $user->id) }}" method="POST" class="form-type-update" data-id="{{ $user->id }}">
                                    @csrf
                                    <div class="form-check form-switch">
                                        <input class="form-check-input approve-switch" type="checkbox" id="approveSwitch{{ $user->id }}" name="approve" {{ $user->player && $user->player->player_type === 'registered' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="approveSwitch{{ $user->id }}">
                                            {{ $user->player->player_type === 'registered' ? 'Registered' : 'Guest' }}
                                        </label>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Player Details Grid -->
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="detail-item mb-3">
                                    <h6 class="text-muted mb-1 fs-5">Player Role</h6>
                                    <p class="mb-0">{{ ucwords(str_replace('-', ' ', $user->player->player_role ?? 'N/A')) }}</p>
                                </div>

                                <div class="detail-item mb-0">
                                    <h6 class="text-muted mb-1 fs-5">Batting Style</h6>
                                    <p class="mb-0">
                                        {{ ucfirst($user->player->batting_style ?? 'N/A') }}
                                        @if ($user->player->batting_style)
                                            <i
                                                class="fa fa-{{ $user->player->batting_style == 'left-handed' ? 'hand-o-left' : 'hand-o-right' }} ms-2"></i>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="detail-item mb-3">
                                    <h6 class="text-muted mb-1 fs-5">Bowling Style</h6>
                                    <p class="mb-0">{{ ucfirst($user->player->bowling_style ?? 'N/A') }}</p>
                                </div>

                                <div class="detail-item mb-0">
                                    <h6 class="text-muted mb-1 fs-5">Player Since</h6>
                                    <p class="mb-0">{{ $user->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Info -->
                        <div class="additional-info mt-3 pt-3 border-top">
                            <div class="row">
                                <div class="col-6 col-md-3 mb-2">
                                    <h6 class="text-muted mb-1 fs-5">Matches</h6>
                                    <p class="mb-0">{{ $user->player->matches ? $user->player->matches->count() : 0 }}
                                    </p>
                                </div>
                                <div class="col-6 col-md-3 mb-2">
                                    <h6 class="text-muted mb-1 fs-5">Runs</h6>
                                    <p class="mb-0">{{ $user->player->total_runs ?? 0 }}</p>
                                </div>
                                <div class="col-6 col-md-3 mb-2">
                                    <h6 class="text-muted mb-1 fs-5">Wickets</h6>
                                    <p class="mb-0">{{ $user->player->total_wickets ?? 0 }}</p>
                                </div>
                                <div class="col-6 col-md-3 mb-2">
                                    <h6 class="text-muted mb-1 fs-5">Highest Score</h6>
                                    <p class="mb-0">{{ $user->player->highest_score ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-sm-3 mb-2 mb-sm-0">
                            <div class="nav flex-column nav-pills" id="vl-pills-tab" role="tablist"
                                aria-orientation="vertical">
                                <a class="nav-link active show" id="vl-pills-home-tab" data-bs-toggle="pill"
                                    href="#vl-pills-home" role="tab" aria-controls="vl-pills-home" aria-selected="true">
                                    <span>About Player</span>
                                </a>
                                <a class="nav-link" id="vl-pills-profile-tab" data-bs-toggle="pill" href="#vl-pills-profile"
                                    role="tab" aria-controls="vl-pills-profile" aria-selected="false">
                                    <span>Statistics</span>
                                </a>
                            </div>
                        </div>

                        <div class="col-sm-9">
                            <div class="tab-content pt-0" id="vl-pills-tabContent">
                                <div class="tab-pane fade active show" id="vl-pills-home" role="tabpanel"
                                    aria-labelledby="vl-pills-home-tab">
                                    <p class="mb-0">{{ $user->bio ?? 'No biography available.' }}</p>
                                </div>
                                <div class="tab-pane fade" id="vl-pills-profile" role="tabpanel"
                                    aria-labelledby="vl-pills-profile-tab">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Match</th>
                                                    <th>Runs</th>
                                                    <th>Wickets</th>
                                                    <th>Highest Score</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($user->player->matches && $user->player->matches->count() > 0)
                                                    @foreach ($user->player->matches as $match)
                                                        <tr>
                                                            <td>{{ $match->name }}</td>
                                                            <td>{{ $match->runs }}</td>
                                                            <td>{{ $match->wickets }}</td>
                                                            <td>{{ $match->highest_score }}</td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="4" class="text-center">No matches played yet.</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.approve-switch').forEach((checkbox) => {
            checkbox.addEventListener('change', function () {
                const form = this.closest('.form-type-update');
                const userId = form.dataset.id;
                const redirectTo = "{{ url()->current() }}"
                const approved = this.checked ? 'on' : 'off'; // mimic what backend expects

                fetch(`/admin/players/approve/${userId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({ approve: approved , redirection: redirectTo})
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if(data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.reload();
                        }
                    } else {
                        alert('Failed to update player status.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Error updating status.');
                });
            });
        });
    </script>
@endpush
