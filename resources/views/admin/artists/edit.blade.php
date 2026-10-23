@extends('layouts.admin')

@section('content')
    <h1 class="mb-4">Редактиране на художник</h1>

    <form method="POST" action="{{ route('admin.artists.update', $artist) }}">
        @csrf
        @method('PUT')
        @include('admin.artists._form')
    </form>
@endsection
