@extends('layouts.app')

@section('filters')
    @if (! request()->routeIs('catalog.category'))
        @include('catalog._filter')
    @endif
@endsection

@section('content')
    <div id="coin-list">
        @include('catalog._list')
    </div>
@endsection
