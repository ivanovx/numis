@extends('layouts.app')

@section('content')
    <div class="container-fluid px-3 px-lg-4 catalog-statistics">
        <header class="catalog-statistics-header mb-4">
            <h1>{{ __('catalog.statistics_title') }}</h1>
            <p class="mb-0">{{ __('catalog.statistics_description') }}</p>
        </header>

        <div class="row row-cols-2 row-cols-lg-4 g-3 mb-4">
            @foreach ([
                ['label' => __('catalog.total_coins'), 'value' => $totalCoins, 'icon' => 'bi-coin'],
                ['label' => __('catalog.total_series'), 'value' => $totalSeries, 'icon' => 'bi-collection'],
                ['label' => __('catalog.total_artists'), 'value' => $totalArtists, 'icon' => 'bi-people'],
                ['label' => __('catalog.missing_images'), 'value' => $missingImages, 'icon' => 'bi-images'],
            ] as $stat)
                <div class="col">
                    <div class="catalog-stat-card h-100">
                        <i class="bi {{ $stat['icon'] }}" aria-hidden="true"></i>
                        <div class="catalog-stat-value">{{ $stat['value'] }}</div>
                        <div class="catalog-stat-label">{{ $stat['label'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <section class="catalog-stat-section h-100">
                    <h2>{{ __('catalog.coins_by_year_chart') }}</h2>
                    <div class="catalog-chart-wrap">
                        <canvas id="chart-coins-by-year" height="220"></canvas>
                    </div>
                </section>
            </div>
            <div class="col-lg-5">
                <section class="catalog-stat-section h-100">
                    <h2>{{ __('catalog.metal_breakdown_title') }}</h2>
                    <div class="catalog-chart-wrap">
                        <canvas id="chart-metals" height="220"></canvas>
                    </div>
                </section>
            </div>
        </div>

        <div class="row g-4 mt-1">
            <div class="col-lg-6">
                <section class="catalog-stat-section h-100">
                    <h2>{{ __('catalog.coins_by_year') }}</h2>
                    <div class="list-group list-group-flush catalog-stat-scroll">
                        @forelse ($coinsByYear as $year => $count)
                            <div class="list-group-item d-flex justify-content-between px-0">
                                @if ($year === 'unknown')
                                    <span>{{ __('catalog.unknown_year') }}</span>
                                @else
                                    <a href="{{ route('catalog.index', ['locale' => app()->getLocale(), 'year_from' => $year, 'year_to' => $year]) }}">
                                        {{ $year }}
                                    </a>
                                @endif
                                <strong>{{ $count }}</strong>
                            </div>
                        @empty
                            <p class="text-muted">{{ __('catalog.no_statistics') }}</p>
                        @endforelse
                    </div>
                </section>
            </div>
            <div class="col-lg-6">
                <section class="catalog-stat-section h-100">
                    <h2>{{ __('catalog.coins_by_category') }}</h2>
                    <div class="list-group list-group-flush">
                        @forelse ($coinsByCategory as $category => $count)
                            <div class="list-group-item d-flex justify-content-between px-0">
                                <span>{{ __('catalog.categories.'.$category) }}</span>
                                <strong>{{ $count }}</strong>
                            </div>
                        @empty
                            <p class="text-muted">{{ __('catalog.no_statistics') }}</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>

        <section class="catalog-stat-section mt-4">
            <h2>{{ __('catalog.data_quality') }}</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="catalog-quality-card">
                        <span>{{ __('catalog.missing_images') }}</span>
                        <strong>{{ $missingImages }}</strong>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="catalog-quality-card">
                        <span>{{ __('catalog.missing_translations') }}</span>
                        <strong>{{ $missingTranslations }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="catalog-stat-section mt-4">
            <div class="d-flex align-items-baseline justify-content-between gap-3 mb-2">
                <h2 class="mb-0">{{ __('catalog.artists_with_coin_counts') }}</h2>
                <span class="small text-muted">{{ $totalArtists }}</span>
            </div>
            <div class="list-group list-group-flush">
                @forelse ($artistsWithCoinCounts as $artist)
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <a href="{{ route('artists.show', ['locale' => app()->getLocale(), 'artist' => $artist]) }}">
                            {{ $artist->name }}
                        </a>
                        <strong>{{ $artist->coins_count }}</strong>
                    </div>
                @empty
                    <p class="text-muted">{{ __('catalog.no_statistics') }}</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            var yearLabels = @json($coinsByYear->reject(fn ($c, $y) => $y === 'unknown')->sortKeys()->keys());
            var yearData = @json($coinsByYear->reject(fn ($c, $y) => $y === 'unknown')->sortKeys()->values());

            var metalLabels = @json($coinsByBaseMetal->keys());
            var metalData = @json($coinsByBaseMetal->values());

            var palette = ['#023E73', '#024873', '#0A89A6', '#15AEBF', '#BF6B04', '#a35a03', '#4b6072', '#7fb8c4'];

            var yearCtx = document.getElementById('chart-coins-by-year');
            if (yearCtx && yearLabels.length) {
                new Chart(yearCtx, {
                    type: 'bar',
                    data: {
                        labels: yearLabels,
                        datasets: [{
                            label: '{{ __('catalog.total_coins') }}',
                            data: yearData,
                            backgroundColor: '#0A89A6',
                            borderRadius: 3,
                            maxBarThickness: 22
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false } },
                            y: { beginAtZero: true, ticks: { precision: 0 } }
                        }
                    }
                });
            }

            var metalCtx = document.getElementById('chart-metals');
            if (metalCtx && metalLabels.length) {
                new Chart(metalCtx, {
                    type: 'doughnut',
                    data: {
                        labels: metalLabels,
                        datasets: [{
                            data: metalData,
                            backgroundColor: palette,
                            borderColor: '#ffffff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } }
                        }
                    }
                });
            }
        })();
    </script>
@endpush
