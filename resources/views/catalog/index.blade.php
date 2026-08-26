@extends('layouts.app')

@section('filters')
    @include('catalog._filter')
@endsection

@section('content')
    <div id="coin-list">
        @include('catalog._list')
    </div>
@endsection
