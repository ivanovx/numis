<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const SUPPORTED = ['bg', 'en', 'de'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if (! in_array($locale, self::SUPPORTED, true)) {
            abort(404);
        }

        App::setLocale($locale);

        // So that route('catalog.index') etc. keep the current locale
        // without every call needing to pass it explicitly.
        URL::defaults(['locale' => $locale]);

        return $next($request);
    }
}
