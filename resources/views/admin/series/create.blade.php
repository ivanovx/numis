@extends('layouts.admin')

@section('content')
    <h1 class="mb-4">Добавяне на серия</h1>

    <form method="POST" action="{{ route('admin.series.store') }}">
        @csrf
        @include('admin.series._form')
    </form>
@endsection
