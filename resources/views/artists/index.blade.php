@extends('layouts.app')

@section('content')
    <div class="container-fluid px-3 px-lg-4 mt-4">
        <h1>Художници</h1>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mt-2">
            @forelse ($artists as $artist)
                <div class="col">
                    <a class="card h-100 shadow-sm text-decoration-none" href="{{ route('artists.show', ['locale' => app()->getLocale(), 'artist' => $artist]) }}">
                        <div class="card-body">
                            <h2 class="h5 card-title text-dark">{{ $artist->name }}</h2>
                            <p class="card-text text-muted mb-0">{{ $artist->coins_count }} монети</p>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-muted">Няма добавени художници.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
