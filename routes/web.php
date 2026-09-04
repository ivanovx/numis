<?php

use App\Http\Controllers\Admin\ArtistController;
use App\Http\Controllers\Admin\CoinController;
use App\Http\Controllers\Admin\CsvController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SeriesController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CatalogController;
use App\Http\Middleware\SetLocale;
use App\Models\Coin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public catalog (replaces the [coin_catalog] shortcode)
|--------------------------------------------------------------------------
| Bulgarian is the default language: "/" redirects to "/bg". "/en" and
| "/de" serve the same catalog translated. The admin panel below is not
| locale-prefixed — it stays in one language for whoever manages the site.
*/
Route::redirect('/', '/'.config('app.locale', 'bg'));

Route::get('/sitemap.xml', function () {
    $urls = collect(SetLocale::SUPPORTED)
        ->map(fn (string $locale) => route('catalog.index', ['locale' => $locale]))
        ->merge(Coin::query()->pluck('id')->flatMap(
            fn (int $id) => collect(SetLocale::SUPPORTED)->map(
                fn (string $locale) => route('catalog.coin', ['locale' => $locale, 'coin' => $id])
            )
        ))
        ->unique()
        ->map(fn (string $url) => '<url><loc>'.e($url).'</loc></url>')
        ->implode('');

    return response('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$urls.'</urlset>', 200, [
        'Content-Type' => 'application/xml',
    ]);
});

Route::get('/graphql-ui', function (Request $request) {
    $locale = $request->query('locale', config('app.locale', 'en'));

    if (in_array($locale, SetLocale::SUPPORTED, true)) {
        App::setLocale($locale);
    }

    return view('graphql-ui');
})->name('graphql.ui');

Route::prefix('{locale}')
    ->whereIn('locale', SetLocale::SUPPORTED)
    ->middleware(SetLocale::class)
    ->group(function () {
        Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
        Route::get('/coin/{coin}', [CatalogController::class, 'show'])->name('catalog.coin');
        Route::get('/statistics', [CatalogController::class, 'statistics'])->name('catalog.statistics');
    });

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'show'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Admin (replaces the Numis WP admin dashboard + coin/series edit screens)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('{resource}/export', [CsvController::class, 'export'])->name('csv.export');
    Route::post('{resource}/import', [CsvController::class, 'import'])->name('csv.import');
    Route::resource('coins', CoinController::class);
    Route::resource('series', SeriesController::class);
    Route::resource('artists', ArtistController::class);
});
