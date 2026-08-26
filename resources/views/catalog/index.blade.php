@extends('layouts.app')

@section('content')
    @include('catalog._filter')

    <div id="coin-list">
        @include('catalog._list')
    </div>
@endsection
