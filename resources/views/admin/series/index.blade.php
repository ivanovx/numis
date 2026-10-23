@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Серии</h1>
        <a href="{{ route('admin.series.create') }}" class="btn btn-primary">➕ Добави серия</a>
    </div>

    @include('admin._csv-tools', ['resource' => 'series'])

    <livewire:admin.series-grid />
@endsection
