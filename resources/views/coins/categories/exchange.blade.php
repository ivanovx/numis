@extends('layouts.app')

@section('content')
    <div class="container-fluid px-3 px-lg-4 mt-4">
        @foreach ($coinsByYear as $year => $coins)
            <section class="mb-5">
                <header class="coins-year-heading">
                    <h2>{{ $year === 'unknown' ? __('catalog.unknown_year') : $year }}</h2>
                    <span>{{ __('catalog.exchange_group') }}</span>
                </header>

                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4">
                    @foreach ($coins as $coin)
                        @include('coins._card', ['coin' => $coin])
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
@endsection
