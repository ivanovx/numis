@php
    if (! ($hasActiveFilters ?? false)) {
        return;
    }

    $labels = [
        'q' => __('catalog.search'),
        'year_from' => __('catalog.year_from'),
        'year_to' => __('catalog.year_to'),
        'category' => __('catalog.category'),
        'metal' => __('catalog.metal'),
        'diameter' => __('catalog.diameter'),
        'denomination' => __('catalog.denomination'),
        'series' => __('catalog.series'),
        'artist' => __('catalog.artist'),
    ];

    $displayValue = function (string $field, string $value) use ($allSeries, $allArtists) {
        if ($field === 'category') {
            return __('catalog.categories.' . $value);
        }
        if ($field === 'series') {
            return $value === 'none'
                ? __('catalog.no_series_option')
                : ($allSeries->firstWhere('slug', $value)->name ?? $value);
        }
        if ($field === 'artist') {
            return $allArtists->firstWhere('slug', $value)->name ?? $value;
        }

        return $value;
    };

    $chips = collect($filters)
        ->filter(fn ($v) => $v !== '' && $v !== null)
        ->map(fn ($value, $field) => [
            'field' => $field,
            'label' => $labels[$field] ?? $field,
            'value' => $displayValue($field, (string) $value),
            'clear_url' => route('catalog.index', collect($filters)->except([$field])->filter()->all()),
        ])
        ->values();
@endphp

@if ($chips->isNotEmpty())
    <div class="container-fluid px-3 px-lg-4 catalog-filter-chips">
        @foreach ($chips as $chip)
            <a href="{{ $chip['clear_url'] }}" class="catalog-chip" data-chip-remove>
                <span class="catalog-chip-label">{{ $chip['label'] }}:</span>
                <span class="catalog-chip-value">{{ $chip['value'] }}</span>
                <i class="bi bi-x" aria-hidden="true"></i>
            </a>
        @endforeach

        <a href="{{ route('catalog.index') }}" class="catalog-chip catalog-chip-clear-all" data-ajax-nav>
            {{ __('catalog.clear_filters') }}
        </a>
    </div>
@endif
