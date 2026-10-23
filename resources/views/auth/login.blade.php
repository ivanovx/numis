<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Вход — администрация на Numis</title>
    @vite('resources/js/bootstrap.js')
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh">
    <div class="container" style="max-width:400px">
        <div class="card p-4 shadow-sm">
            <h1 class="h4 mb-3 text-center">Вход в администрацията</h1>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Имейл адрес</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Парола</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Запомни ме</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">Вход</button>
            </form>
        </div>
    </div>
</body>
</html>
