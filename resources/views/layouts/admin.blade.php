<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Numis Admin' }}</title>
    @vite('resources/js/bootstrap.js')
    <link href="{{ asset('css/admin.css') }}?v=3" rel="stylesheet">
    @vite('resources/js/admin.js')
    @livewireStyles
</head>
<body class="admin-shell">

    <nav class="navbar navbar-expand-lg navbar-dark admin-navbar mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">Numis Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavigation"
                    aria-controls="adminNavigation" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNavigation">
                <div class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <a class="nav-link {{ request()->routeIs('admin.coins.*') ? 'active' : '' }}" href="{{ route('admin.coins.index') }}">Coins</a>
                    <a class="nav-link {{ request()->routeIs('admin.series.*') ? 'active' : '' }}" href="{{ route('admin.series.index') }}">Series</a>
                    <a class="nav-link {{ request()->routeIs('admin.artists.*') ? 'active' : '' }}" href="{{ route('admin.artists.index') }}">Artists</a>
                    <form method="POST" action="{{ route('logout') }}" class="d-lg-inline mt-2 mt-lg-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="container admin-main pb-5">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    @include('partials.footer')

    <style>
        .rich-text-editor {
            background: #fff;
            border: 1px solid #e5ddd7;
            border-radius: 0.75rem;
            overflow: hidden;
            margin-bottom: 0.75rem;
        }

        .rich-text-editor .ql-toolbar {
            border: 0;
            border-bottom: 1px solid #e5ddd7;
            background: #f9f6f3;
        }

        .rich-text-editor .ql-container,
        .rich-text-editor .ql-editor {
            min-height: 140px;
            max-height: 220px;
            overflow: auto;
        }

        .admin-shell .form-control[type="file"] {
            margin-top: 0.5rem;
        }
    </style>
    @livewireScripts
</body>
</html>
