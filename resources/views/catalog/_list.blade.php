@php
    $hasActiveFilters = collect($filters)->filter(fn ($v) => $v !== '' && $v !== null)->isNotEmpty();
    $isHomeContext = ! request()->routeIs('catalog.category') && ! $hasActiveFilters && ! request()->filled('page');
@endphp

@include('catalog._hero', ['show' => $isHomeContext])
@include('catalog._chips', ['hasActiveFilters' => $hasActiveFilters])

@if ($isHomeContext && ($recentCoins ?? collect())->isNotEmpty())
    <section class="catalog-recent container-fluid px-3 px-lg-4 mt-4">
        <h2 class="catalog-recent-title">{{ __('catalog.recent_title') }}</h2>
        <div class="catalog-recent-track">
            @foreach ($recentCoins as $coin)
                <a href="{{ route('catalog.coin', ['locale' => app()->getLocale(), 'coin' => $coin]) }}" class="catalog-recent-item">
                    <div class="catalog-recent-thumb">
                        @if ($coin->front_image_url)
                            <img src="{{ $coin->front_image_url }}" alt="{{ $coin->title }}" loading="lazy">
                        @endif
                    </div>
                    <div class="catalog-recent-name">{{ $coin->title }}</div>
                    <div class="catalog-recent-year">{{ $coin->year }}</div>
                </a>
            @endforeach
        </div>
    </section>
@endif

@if ($coins->count())

<div class="container-fluid px-3 px-lg-4 mt-4">
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
        @php($previousYear = '__initial__')
        @php($groupedCategory = in_array(($filters['category'] ?? ''), ['collectible', 'commemorative'], true))
        @php($exchangeGroups = (($filters['category'] ?? '') === 'exchange') ? collect($coins->items())->sort(function ($left, $right) {
            $leftYear = (int) ($left->year ?? 0);
            $rightYear = (int) ($right->year ?? 0);

            if ($leftYear !== $rightYear) {
                return $rightYear <=> $leftYear;
            }

            preg_match('/-?\d+(?:[.,]\d+)?/', (string) ($left->denomination ?? ''), $leftMatches);
            preg_match('/-?\d+(?:[.,]\d+)?/', (string) ($right->denomination ?? ''), $rightMatches);

            return ((float) str_replace(',', '.', $leftMatches[0] ?? '0')) <=> ((float) str_replace(',', '.', $rightMatches[0] ?? '0'));
        })->groupBy(fn ($coin) => $coin->year ?: 'unknown')->sortKeysDesc() : null)
        @php($seriesGroups = $groupedCategory ? collect($coins->items())->groupBy(fn ($coin) => $coin->series?->name ?? __('catalog.no_series')) : null)

        @if ($exchangeGroups)
            @foreach ($exchangeGroups as $year => $yearCoins)
                <div class="col-12 catalog-year-heading">
                    <h2>{{ $year === 'unknown' ? __('catalog.unknown_year') : $year }}</h2>
                    <span>{{ __('catalog.exchange_group') }}</span>
                </div>

                @foreach ($yearCoins as $coin)
                    @include('catalog._card', ['coin' => $coin])
                @endforeach
            @endforeach
        @elseif ($seriesGroups)
            @foreach ($seriesGroups as $seriesName => $seriesCoins)
                <div class="col-12 catalog-year-heading">
                    <h2>{{ $seriesName }}</h2>
                </div>

                @foreach ($seriesCoins as $coin)
                    @include('catalog._card', ['coin' => $coin])
                @endforeach
            @endforeach
        @else
            @foreach ($coins as $coin)
                @php($coinYear = $coin->year ?: 'unknown')
                @if (($filters['category'] ?? '') === 'exchange' && $previousYear !== $coinYear)
                    <div class="col-12 catalog-year-heading">
                        <h2>{{ $coinYear === 'unknown' ? __('catalog.unknown_year') : $coinYear }}</h2>
                        <span>{{ __('catalog.exchange_group') }}</span>
                    </div>
                    @php($previousYear = $coinYear)
                @endif

                @include('catalog._card', ['coin' => $coin])
            @endforeach
        @endif

    </div>
</div>

<div class="container-fluid px-3 px-lg-4 catalog-pagination py-4">
    <div class="d-flex justify-content-center">
        {{ $coins->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
</div>

@else
    <div class="catalog-empty-state">
        <svg viewBox="0 0 64 64" width="72" height="72" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <circle cx="26" cy="26" r="16" stroke="currentColor" stroke-width="3"/>
            <circle cx="26" cy="26" r="10" stroke="currentColor" stroke-width="1.5" stroke-dasharray="2 3"/>
            <line x1="37.5" y1="37.5" x2="52" y2="52" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
        </svg>
        <p class="catalog-empty-title">{{ __('catalog.no_coins_found') }}</p>
        <p class="catalog-empty-subtitle">{{ __('catalog.no_coins_hint') }}</p>
        <a href="{{ route('catalog.index') }}" class="btn btn-primary" data-ajax-nav>{{ __('catalog.clear_filters') }}</a>
    </div>
@endif
