<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? __('catalog.site_title') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/catalog.css') }}" rel="stylesheet">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container justify-content-between">
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

    <main>
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/catalog.js') }}"></script>
</body>
</html>
