@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Исторически периоди</h1>
        <a href="{{ route('admin.periods.create') }}" class="btn btn-primary">➕ Добави период</a>
    </div>

    <livewire:admin.periods-grid />
@endsection
