@extends('layouts.app')

@php($currentLocale = app()->getLocale())

@section('content')
    <div class="container my-5">
        <h1 class="display-3 fw-bold text-center mb-5">Очаквайте скоро</h1>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4">
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <img src="{{ asset('images/categories/razmenni.jpg') }}" class="card-img-top" alt="Разменни" style="height: 160px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Разменни</h5>
                        <p class="card-text text-muted small flex-grow-1">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore.
                        </p>
                        <a href="{{ route('catalog.category', ['locale' => $currentLocale, 'category' => 'razmenni']) }}" class="btn btn-outline-primary btn-sm mt-auto">
                            Разгледай
                        </a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card h-100 shadow-sm">
                    <img src="{{ asset('images/categories/vazpomenatelni.jpg') }}" class="card-img-top" alt="Възпоменателни" style="height: 160px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Възпоменателни</h5>
                        <p class="card-text text-muted small flex-grow-1">
                            Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo.
                        </p>
                        <a href="{{ route('catalog.category', ['locale' => $currentLocale, 'category' => 'vazpomenatelni']) }}" class="btn btn-outline-primary btn-sm mt-auto">
                            Разгледай
                        </a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card h-100 shadow-sm">
                    <img src="{{ asset('images/categories/kolekcionerski.jpg') }}" class="card-img-top" alt="Колекционерски" style="height: 160px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Колекционерски</h5>
                        <p class="card-text text-muted small flex-grow-1">
                            Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla.
                        </p>
                        <a href="{{ route('catalog.category', ['locale' => $currentLocale, 'category' => 'kolekcionerski']) }}" class="btn btn-outline-primary btn-sm mt-auto">
                            Разгледай
                        </a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card h-100 shadow-sm">
                    <img src="{{ asset('images/categories/probni.jpg') }}" class="card-img-top" alt="Пробни" style="height: 160px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Пробни</h5>
                        <p class="card-text text-muted small flex-grow-1">
                            Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim.
                        </p>
                        <a href="{{ route('catalog.category', ['locale' => $currentLocale, 'category' => 'probni']) }}" class="btn btn-outline-primary btn-sm mt-auto">
                            Разгледай
                        </a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card h-100 shadow-sm">
                    <img src="{{ asset('images/categories/kuriozi.jpg') }}" class="card-img-top" alt="Куриози" style="height: 160px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Куриози</h5>
                        <p class="card-text text-muted small flex-grow-1">
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium.
                        </p>
                        <a href="{{ route('catalog.category', ['locale' => $currentLocale, 'category' => 'kuriozi']) }}" class="btn btn-outline-primary btn-sm mt-auto">
                            Разгледай
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
