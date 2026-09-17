@extends('layouts.app')

@section('content')
    <div class="container-fluid px-3 px-lg-4 mt-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <a href="{{ route('artists.index', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-secondary">
                &larr; Художници
            </a>
        </div>

        <header class="mb-4">
            <h1>{{ $artist->name }}</h1>
            <p class="text-muted mb-0">{{ $artist->coins->count() }} монети</p>
        </header>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4">
            @forelse ($artist->coins as $coin)
                @include('coins._card', ['coin' => $coin])
            @empty
                <div class="col-12">
                    <p class="text-muted">Този художник няма свързани монети.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
