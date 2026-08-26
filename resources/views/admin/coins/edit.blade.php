@extends('layouts.admin')

@section('content')
    <h1 class="mb-4">Edit Coin</h1>

    <form method="POST" action="{{ route('admin.coins.update', $coin) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.coins._form', ['selected' => old('series', $selected)])
    </form>
@endsection
