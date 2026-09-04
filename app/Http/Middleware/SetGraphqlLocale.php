<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetGraphqlLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->input('locale', $request->header('Accept-Language'));
        $locale = is_string($locale) ? strtolower(substr($locale, 0, 2)) : null;

        if ($locale && in_array($locale, SetLocale::SUPPORTED, true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}