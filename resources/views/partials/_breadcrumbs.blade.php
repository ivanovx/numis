@php
    $items = collect($items ?? [])->filter(fn ($item) => filled($item['label'] ?? null))->values();
@endphp

@if ($items->isNotEmpty())
    <nav aria-label="breadcrumb" class="catalog-breadcrumbs container-fluid px-3 px-lg-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('home.locale', ['locale' => app()->getLocale()]) }}">{{ __('catalog.home_nav') }}</a>
            </li>
            @foreach ($items as $item)
                @if (! $loop->last && ($item['url'] ?? null))
                    <li class="breadcrumb-item"><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                @else
                    <li class="breadcrumb-item active" aria-current="page">{{ $item['label'] }}</li>
                @endif
            @endforeach
        </ol>
    </nav>

    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect([['label' => __('catalog.home_nav'), 'url' => route('home.locale', ['locale' => app()->getLocale()])]])
                ->concat($items)
                ->values()
                ->map(fn ($item, $index) => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['label'],
                    'item' => $item['url'] ?? url()->current(),
                ])->all(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
@endif
