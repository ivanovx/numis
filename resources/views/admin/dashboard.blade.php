@extends('layouts.admin')

@section('content')
    <h1 class="mb-4">Numis Dashboard</h1>

    <div class="mb-4 d-flex gap-2">
        <a href="{{ route('admin.coins.create') }}" class="btn btn-primary">➕ Add Coin</a>
        <a href="{{ route('admin.series.create') }}" class="btn btn-secondary">➕ Add Series</a>
        <a href="{{ route('admin.artists.create') }}" class="btn btn-secondary">➕ Add Artist</a>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card text-center p-3">
                <h2>{{ $coinCount }}</h2>
                <p class="mb-0">Coins</p>
                <a href="{{ route('admin.coins.index') }}">View all</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center p-3">
                <h2>{{ $seriesCount }}</h2>
                <p class="mb-0">Series</p>
                <a href="{{ route('admin.series.index') }}">View all</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center p-3">
                <h2>{{ $artistCount }}</h2>
                <p class="mb-0">Artists</p>
                <a href="{{ route('admin.artists.index') }}">View all</a>
            </div>
        </div>
    </div>

    @if ($completeness['total'] > 0)
        @php
            $avgTone = $completeness['average_percent'] >= 80 ? 'success' : ($completeness['average_percent'] >= 50 ? 'warning' : 'danger');
            $gaps = collect($completeness['missing'])->sortDesc();
        @endphp

        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <h2 class="h5 mb-0">Data completeness (Пълнота на данните)</h2>
                    <span class="text-muted small">
                        {{ $completeness['complete'] }} of {{ $completeness['total'] }} coins have all {{ count($completenessFields) }} tracked fields
                        · <a href="{{ route('admin.coins.index', ['completeness' => 'incomplete']) }}">show incomplete</a>
                    </span>
                </div>

                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="progress flex-grow-1" style="height: .75rem" role="progressbar"
                         aria-valuenow="{{ $completeness['average_percent'] }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar bg-{{ $avgTone }}" style="width: {{ $completeness['average_percent'] }}%"></div>
                    </div>
                    <strong>{{ $completeness['average_percent'] }}%</strong>
                </div>

                <h3 class="h6 text-muted">Most common gaps (Най-често липсва)</h3>
                <div class="row g-2">
                    @foreach ($gaps as $field => $missingCount)
                        <div class="col-md-6 col-lg-4">
                            <a href="{{ route('admin.coins.index', ['missing' => $field]) }}"
                               class="d-flex justify-content-between align-items-center border rounded px-3 py-2 text-decoration-none text-dark {{ $missingCount === 0 ? 'bg-light text-muted' : '' }}">
                                <span class="small">{{ $completenessFields[$field] }}</span>
                                <span class="badge {{ $missingCount === 0 ? 'text-bg-success' : 'text-bg-warning' }}">{{ $missingCount === 0 ? '✓' : $missingCount }}</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endsection
