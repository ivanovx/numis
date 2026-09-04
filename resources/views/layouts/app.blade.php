<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seoTitle ?? __('catalog.seo_title') }}</title>
    <meta name="description" content="{{ $seoDescription ?? __('catalog.seo_description') }}">
    <meta name="robots" content="{{ request()->except('page') ? 'noindex,follow' : 'index,follow' }}">
    <link rel="canonical" href="{{ $canonicalUrl ?? route('catalog.index', request()->integer('page') > 1 ? ['page' => request()->integer('page')] : []) }}">
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
    <link href="{{ asset('css/catalog.css') }}?v={{ filemtime(public_path('css/catalog.css')) }}-2" rel="stylesheet">
</head>
<body>

    <nav id="site-navbar" class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid px-3 px-lg-4 justify-content-between">
            <a class="navbar-brand" href="{{ route('catalog.index') }}">{{ __('catalog.site_title') }}</a>
            <div class="d-flex gap-2">
                @foreach (['bg' => 'БГ', 'en' => 'EN', 'de' => 'DE'] as $code => $label)
                    <a href="{{ url()->to(preg_replace('#^/(bg|en|de)#', '/' . $code, request()->getRequestUri())) }}"
                       class="btn btn-sm {{ app()->getLocale() === $code ? 'btn-light' : 'btn-outline-light' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </nav>

    @yield('filters')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/catalog.js') }}?v={{ filemtime(public_path('js/catalog.js')) }}-2"></script>
</body>
</html>
