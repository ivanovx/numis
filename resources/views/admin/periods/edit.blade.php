@extends('layouts.admin')

@section('content')
    <h1 class="mb-4">Редактиране на исторически период</h1>

    <form method="POST" action="{{ route('admin.periods.update', $period) }}">
        @csrf
        @method('PUT')
        @include('admin.periods._form')
    </form>
@endsection
