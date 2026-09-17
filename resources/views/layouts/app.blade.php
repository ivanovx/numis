<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seoTitle ?? __('catalog.seo_title') }}</title>
    <meta name="description" content="{{ $seoDescription ?? __('catalog.seo_description') }}">
    <meta name="robots" content="{{ request()->except('page') ? 'noindex,follow' : 'index,follow' }}">
    @php($catalogRoute = request()->route('locale') ? 'catalog.index' : 'catalog.page')
    <link rel="canonical" href="{{ $canonicalUrl ?? route($catalogRoute, request()->integer('page') > 1 ? ['page' => request()->integer('page')] : []) }}">
    @foreach (['bg', 'en', 'de'] as $locale)
        <link rel="alternate" hreflang="{{ $locale }}" href="{{ $alternateUrls[$locale] ?? route('catalog.index', ['locale' => $locale]) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ route('catalog.index', ['locale' => 'bg']) }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ __('catalog.site_title') }}">
    <meta property="og:title" content="{{ $seoTitle ?? __('catalog.seo_title') }}">
    <meta property="og:description" content="{{ $seoDescription ?? __('catalog.seo_description') }}">
    <meta property="og:url" content="{{ $canonicalUrl ?? url()->current() }}">
    @if (! empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $seoTitle ?? __('catalog.seo_title') }}">
    <meta name="twitter:description" content="{{ $seoDescription ?? __('catalog.seo_description') }}">
    @php($structuredData = $structuredData ?? [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => __('catalog.seo_title'),
        'description' => __('catalog.seo_description'),
        'url' => $canonicalUrl ?? url()->current(),
        'inLanguage' => app()->getLocale(),
    ])
    <script type="application/ld+json">
        @json($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/catalog.css') }}?v={{ filemtime(public_path('css/catalog.css')) }}-4" rel="stylesheet">
</head>
<body>
    @php($currentLocale = app()->getLocale())

    <nav id="site-navbar" class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid px-3 px-lg-4 d-flex align-items-center">
            <a class="navbar-brand me-3" href="{{ route('home.locale', ['locale' => $currentLocale]) }}">{{ __('catalog.site_title') }}</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#site-navbar-menu"
                    aria-controls="site-navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div id="site-navbar-menu" class="collapse navbar-collapse">
                <div class="navbar-nav align-items-lg-center gap-lg-3 ms-lg-4">
                    <a href="{{ route('home.locale', ['locale' => $currentLocale]) }}"
                       class="nav-link {{ request()->routeIs('home', 'home.locale') ? 'active text-white fw-semibold' : 'text-white-50' }}">
                        {{ __('catalog.home_nav') }}
                    </a>

                    <a href="{{ route('catalog.index', ['locale' => $currentLocale]) }}"
                       class="nav-link {{ request()->routeIs('catalog.index', 'catalog.page') ? 'active text-white fw-semibold' : 'text-white-50' }}">
                        {{ __('catalog.catalog_nav') }}
                    </a>

                    <a href="{{ route('artists.index', ['locale' => $currentLocale]) }}"
                       class="nav-link {{ request()->routeIs('artists.index', 'artists.show') ? 'active text-white fw-semibold' : 'text-white-50' }}">
                        {{ __('catalog.artists_nav') }}
                    </a>

                    <div class="nav-item dropdown">
                        <a class="btn btn-link nav-link dropdown-toggle text-white-50 p-0 border-0 {{ request()->routeIs('coins.index', 'catalog.category') ? 'active text-white fw-semibold' : '' }}"
                           href="{{ route('coins.index', ['locale' => $currentLocale]) }}"
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Монети
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li>
                                <a class="dropdown-item" href="{{ route('coins.index', ['locale' => $currentLocale]) }}">
                                    {{ __('catalog.catalog_nav') }}
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            @foreach (\App\Models\Coin::CATEGORIES as $category)
                                <li>
                                    <a class="dropdown-item {{ request()->route('category') === $category ? 'active' : '' }}" href="{{ route('catalog.category', ['locale' => $currentLocale, 'category' => $category]) }}">
                                        {{ __('catalog.categories.' . $category) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <a href="{{ route('catalog.statistics', ['locale' => $currentLocale]) }}"
                       class="nav-link {{ request()->routeIs('catalog.statistics') ? 'active text-white fw-semibold' : 'text-white-50' }}">
                        {{ __('catalog.statistics_nav') }}
                    </a>
                </div>

                <div class="ms-lg-auto mt-3 mt-lg-0">
                    <label for="locale-switcher" class="visually-hidden">Език</label>
                    <select id="locale-switcher" class="form-select form-select-sm w-auto bg-dark text-white border-secondary" aria-label="Language selector" onchange="window.location.href = switchLocale(this.value)">
                        @foreach (['bg' => 'Български', 'en' => 'English', 'de' => 'Deutsch'] as $code => $label)
                            <option value="{{ $code }}" {{ app()->getLocale() === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </nav>

    <script>
        function switchLocale(locale) {
            const currentPath = window.location.pathname;
            const currentQuery = window.location.search;

            if (currentPath === '/') {
                return '/' + locale + currentQuery;
            }

            const nextPath = currentPath.replace(/^\/(bg|en|de)(?=\/|$)/, '/' + locale);

            return nextPath + currentQuery;
        }
    </script>

    @yield('filters')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/catalog.js') }}?v={{ filemtime(public_path('js/catalog.js')) }}-2"></script>
</body>
</html>
