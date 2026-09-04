@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Artists</h1>
        <a href="{{ route('admin.artists.create') }}" class="btn btn-primary">➕ Add Artist</a>
    </div>

    @include('admin._csv-tools', ['resource' => 'artists'])

    <livewire:admin.artists-grid />
@endsection
