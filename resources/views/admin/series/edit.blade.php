@extends('layouts.admin')

@section('content')
    <h1 class="mb-4">Edit Series</h1>

    <form method="POST" action="{{ route('admin.series.update', $series) }}">
        @csrf
        @method('PUT')
        @include('admin.series._form')
    </form>
@endsection
