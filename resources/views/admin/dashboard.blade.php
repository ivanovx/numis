@extends('layouts.admin')

@section('content')
    <h1 class="mb-4">Numis Dashboard</h1>

    <div class="mb-4 d-flex gap-2">
        <a href="{{ route('admin.coins.create') }}" class="btn btn-primary">➕ Add Coin</a>
        <a href="{{ route('admin.series.create') }}" class="btn btn-secondary">➕ Add Series</a>
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
    </div>
@endsection
