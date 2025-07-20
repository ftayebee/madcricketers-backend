@extends('admin.layouts.theme')

@section('content')
    @push('styles')
    @endpush
    <div class="row">
        <div class="col-sm-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-4 align-items-center">
                        {{-- Tournament Logo --}}
                        <div class="col-md-2 text-center">
                            <div class="rounded border p-2 bg-light">
                                @if ($match->tournament && $match->tournament->logo)
                                    <img src="{{ asset('storage/uploads/tournaments/' . $match->tournament->logo) }}"
                                        class="img-fluid rounded" style="max-height: 100px;"
                                        alt="{{ $match->tournament->name }}">
                                @else
                                    <div class="text-muted small">No Logo</div>
                                @endif
                            </div>
                        </div>

                        {{-- Match Info --}}
                        <div class="col-md-10">
                            <h4 class="fw-bold mb-2">
                                {{ $match->teamA->name ?? 'Team A' }}
                                <span class="text-muted">vs</span>
                                {{ $match->teamB->name ?? 'Team B' }}
                            </h4>

                            <p class="mb-1"><strong>Match Date:</strong>
                                {{ \Carbon\Carbon::parse($match->date)->format('d M, Y') ?? 'N/A' }}
                            </p>
                            <p class="mb-1"><strong>Match Status:</strong>
                                <span
                                    class="badge bg-{{ $match->status == 'completed' ? 'success' : ($match->status == 'live' ? 'danger' : 'secondary') }}">
                                    {{ ucfirst($match->status) }}
                                </span>
                            </p>

                            {{-- Tournament Info (Compact) --}}
                            <p class="mb-1"><strong>Tournament:</strong>
                                {{ $match->tournament->name ?? 'N/A' }}
                                <span
                                    class="badge bg-{{ $match->tournament->status == 'completed' ? 'success' : ($match->tournament->status == 'ongoing' ? 'primary' : 'secondary') }}">
                                    {{ ucfirst($match->tournament->status) }}
                                </span>
                            </p>
                            <p class="mb-1"><strong>Location:</strong> {{ $match->tournament->location ?? 'N/A' }}</p>
                            <p class="text-muted small mb-0">
                                {{ $match->tournament->description ?? 'No description available.' }}</p>
                        </div>
                    </div>
                </div>
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
