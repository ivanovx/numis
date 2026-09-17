@extends('layouts.app')

@section('content')
  <h1>home</h1>
  <a href="{{ route('catalog.index', ['locale' => app()->getLocale()]) }}">Каталог</a>
@endsection
