<div class="container-fluid px-3 px-lg-4 mt-4">
    @if ($seriesGroups)
        @foreach ($seriesGroups as $seriesName => $seriesCoins)
            <section class="mb-5">
                <header class="coins-year-heading">
                    <h2>{{ $seriesName }}</h2>
                </header>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4">
                    @foreach ($seriesCoins as $coin)
                        @include('coins._card', ['coin' => $coin])
                    @endforeach
                </div>
            </section>
        @endforeach
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4">
            @foreach ($coins as $coin)
                @include('coins._card', ['coin' => $coin])
            @endforeach
        </div>
    @endif
</div>
