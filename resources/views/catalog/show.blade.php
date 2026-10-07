@extends('layouts.app')

@section('content')
    @include('partials._breadcrumbs', ['items' => $breadcrumbItems])

    @if ($neighborCoins['prev'])
        <a href="{{ route('catalog.coin', array_merge(['locale' => app()->getLocale(), 'coin' => $neighborCoins['prev']], array_filter($contextFilters))) }}"
           class="catalog-neighbor-arrow catalog-neighbor-prev" title="{{ $neighborCoins['prev']->title }}" aria-label="{{ __('catalog.previous_coin') }}">
            <i class="bi bi-chevron-left" aria-hidden="true"></i>
        </a>
    @endif

    @if ($neighborCoins['next'])
        <a href="{{ route('catalog.coin', array_merge(['locale' => app()->getLocale(), 'coin' => $neighborCoins['next']], array_filter($contextFilters))) }}"
           class="catalog-neighbor-arrow catalog-neighbor-next" title="{{ $neighborCoins['next']->title }}" aria-label="{{ __('catalog.next_coin') }}">
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
        </a>
    @endif

    <div class="container-fluid px-3 px-lg-4 catalog-detail">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <a href="{{ url()->previous() === url()->current() ? route('catalog.index') : url()->previous() }}" class="btn btn-outline-secondary">
                &larr; {{ __('catalog.back_to_catalog') }}
            </a>

            <div class="d-flex align-items-center gap-3">
                @if ($coin->series)
                    <span class="catalog-detail-kicker">{{ $coin->series->name }}</span>
                @endif

                <button type="button" class="btn btn-outline-secondary catalog-share-btn" id="catalog-share-btn"
                        data-share-url="{{ $canonicalUrl }}" title="{{ __('catalog.copy_link') }}">
                    <i class="bi bi-share" aria-hidden="true"></i>
                    <span class="catalog-share-copied" id="catalog-share-copied">{{ __('catalog.link_copied') }}</span>
                </button>
            </div>
        </div>

        <article class="catalog-detail-card">
            <header class="catalog-detail-header">
                <p class="catalog-detail-eyebrow mb-2">{{ $coin->category ? __('catalog.categories.' . $coin->category) : '' }}</p>
                <h1>{{ $coin->title }}</h1>
                @if ($coin->year)
                    <p class="mb-0">{{ __('catalog.year_label') }}: {{ $coin->year }}</p>
                @endif
            </header>

            <div class="row g-0">
                <div class="col-lg-5 catalog-detail-images">
                    <div class="row g-3">
                        @foreach ([['image' => $coin->front_image_url, 'label' => __('catalog.front_label'), 'description' => $coin->front_description], ['image' => $coin->back_image_url, 'label' => __('catalog.back_label'), 'description' => $coin->back_description]] as $side)
                            <div class="col-6 text-center">
                                <button type="button" class="catalog-detail-image-frame catalog-zoomable" @if ($side['image']) data-lightbox-src="{{ $side['image'] }}" data-lightbox-alt="{{ $coin->title }} — {{ $side['label'] }}" @endif>
                                    @if ($side['image'])
                                        <img src="{{ $side['image'] }}" class="img-fluid" alt="{{ $coin->title }} — {{ $side['label'] }}">
                                        <span class="catalog-zoom-hint"><i class="bi bi-zoom-in" aria-hidden="true"></i></span>
                                    @else
                                        <span class="text-muted">{{ $side['label'] }}</span>
                                    @endif
                                </button>
                                <div class="small text-muted mt-2">{{ $side['label'] }}</div>
                                @if ($side['description'])
                                    <div class="catalog-rich-text text-start mt-2">{!! $side['description'] !!}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-7 catalog-detail-content">
                    <dl class="row catalog-spec-list mb-0">
                        @foreach ([
                            'year' => __('catalog.year_label'),
                            'issue_date' => __('catalog.issue_date_label'),
                            'denomination' => __('catalog.denomination_label'),
                            'metal' => __('catalog.metal_label'),
                            'quality' => __('catalog.quality_label'),
                            'weight' => __('catalog.weight_label'),
                            'diameter' => __('catalog.diameter_label'),
                            'edge' => __('catalog.edge_label'),
                            'mintage' => __('catalog.mintage_label'),
                            'mint' => __('catalog.mint_label'),
                        ] as $field => $label)
                            @php($value = $field === 'issue_date' ? $coin->issue_date?->format('d.m.Y') : $coin->$field)
                            @if ($value)
                                <dt class="col-sm-5">{{ $label }}</dt>
                                <dd class="col-sm-7">{{ $value }}{{ $field === 'diameter' ? ' mm' : '' }}</dd>
                            @endif
                        @endforeach

                        @if ($coin->artists->isNotEmpty())
                            <dt class="col-sm-5">{{ __('catalog.artist_label') }}</dt>
                            <dd class="col-sm-7">{{ $coin->artistNames() }}</dd>
                        @endif
                        <dt class="col-sm-5">{{ __('catalog.series_label') }}</dt>
                        <dd class="col-sm-7">{{ $coin->series?->name ?? __('catalog.no_series') }}</dd>
                    </dl>

                    @if ($coin->description)
                        <section class="catalog-rich-text catalog-detail-description mt-4">
                            {!! $coin->description !!}
                        </section>
                    @endif
                </div>
            </div>
        </article>

        @if ($coin->series && $seriesTimeline->count() > 1)
            <section class="catalog-series-timeline">
                <h2 class="catalog-similar-title">{{ $coin->series->name }} — {{ __('catalog.series_timeline_title') }}</h2>
                <div class="catalog-timeline-track">
                    @foreach ($seriesTimeline as $timelineCoin)
                        <a href="{{ route('catalog.coin', ['locale' => app()->getLocale(), 'coin' => $timelineCoin]) }}"
                           class="catalog-timeline-item {{ $timelineCoin->id === $coin->id ? 'is-current' : '' }}">
                            <div class="catalog-timeline-thumb">
                                @if ($timelineCoin->front_image_url)
                                    <img src="{{ $timelineCoin->front_image_url }}" alt="{{ $timelineCoin->title }}" loading="lazy">
                                @endif
                            </div>
                            <span class="catalog-timeline-year">{{ $timelineCoin->year ?: '—' }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($similarCoins->isNotEmpty())
            <section class="catalog-similar">
                <h2 class="catalog-similar-title">{{ __('catalog.similar_coins_title') }}</h2>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
                    @foreach ($similarCoins as $similarCoin)
                        @include('catalog._card', ['coin' => $similarCoin])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var btn = document.getElementById('catalog-share-btn');
            var tooltip = document.getElementById('catalog-share-copied');
            if (!btn) return;

            btn.addEventListener('click', function () {
                var url = btn.getAttribute('data-share-url');

                var showCopied = function () {
                    tooltip.classList.add('is-visible');
                    setTimeout(function () { tooltip.classList.remove('is-visible'); }, 1600);
                };

                if (navigator.share) {
                    navigator.share({ url: url }).catch(function () {});
                    return;
                }

                if (navigator.clipboard) {
                    navigator.clipboard.writeText(url).then(showCopied);
                } else {
                    var input = document.createElement('input');
                    input.value = url;
                    document.body.appendChild(input);
                    input.select();
                    document.execCommand('copy');
                    document.body.removeChild(input);
                    showCopied();
                }
            });
        })();
    </script>
@endpush
