@extends('layouts.app')

@section('content')
    <div class="container-fluid px-3 px-lg-4 mt-4">
        <h1>{{ __('catalog.catalog_nav') }}</h1>

        <div class="list-group mt-4">
            @foreach ($categories as $category)
                <a class="list-group-item list-group-item-action"
                   href="{{ route('catalog.category', ['locale' => app()->getLocale(), 'category' => $category]) }}">
                    {{ __("catalog.categories.{$category}") }}
                </a>
            @endforeach
        </div>
    </div>
@endsection
