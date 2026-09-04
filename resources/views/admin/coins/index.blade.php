@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Coins</h1>
        <a href="{{ route('admin.coins.create') }}" class="btn btn-primary">➕ Add Coin</a>
    </div>

    @include('admin._csv-tools', ['resource' => 'coins'])

    <livewire:admin.coins-grid />
@endsection
