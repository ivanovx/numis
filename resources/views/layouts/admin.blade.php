<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Numis Admin' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}?v=1" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-rich-text-editor]').forEach((editorElement) => {
                const input = document.getElementById(editorElement.dataset.richTextEditor);
                const editor = new Quill(editorElement, {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ header: [2, 3, false] }],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['blockquote'],
                            ['clean'],
                        ],
                    },
                });

                if (input.value) {
                    editor.clipboard.dangerouslyPasteHTML(input.value);
                }

                editor.on('text-change', () => {
                    input.value = editor.root.innerHTML;
                });

                editorElement.closest('form')?.addEventListener('submit', () => {
                    input.value = editor.root.innerHTML;
                });
            });
        });
    </script>
    @livewireScripts
</body>
</html>
