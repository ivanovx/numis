@extends('layouts.admin')

@section('content')
    <h1 class="mb-4">Add Artist</h1>

    <form method="POST" action="{{ route('admin.artists.store') }}">
        @csrf
        @include('admin.artists._form')
    </form>
@endsection
