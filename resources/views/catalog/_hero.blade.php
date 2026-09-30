@if ($show ?? false)
    <section class="catalog-hero">
        <div class="container-fluid px-3 px-lg-4">
            <p class="catalog-hero-eyebrow">{{ __('catalog.hero_eyebrow') }}</p>
            <h1 class="catalog-hero-title">{{ __('catalog.hero_title') }}</h1>
            <p class="catalog-hero-subtitle">
                {{ __('catalog.hero_subtitle', ['count' => $totalCoins, 'year' => $earliestYear]) }}
            </p>
        </div>
    </section>
@endif
