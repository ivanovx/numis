@extends('layouts.admin')

@section('content')
    <h1 class="mb-4">Add Coin</h1>

    <form method="POST" action="{{ route('admin.coins.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.coins._form', ['selected' => old('series', [])])
    </form>
@endsection
