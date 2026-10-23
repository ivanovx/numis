@extends('layouts.admin')

@section('content')
    <h1 class="mb-4">Добавяне на исторически период</h1>

    <form method="POST" action="{{ route('admin.periods.store') }}">
        @csrf
        @include('admin.periods._form')
    </form>
@endsection
