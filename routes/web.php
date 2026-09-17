<?php

use App\Http\Controllers\Admin\ArtistController;
use App\Http\Controllers\Admin\CoinController;
use App\Http\Controllers\Admin\CsvController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SeriesController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ArtistsController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CoinsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StatisticsController;
use App\Http\Middleware\SetLocale;
use App\Models\Artist;
use App\Models\Coin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public catalog (replaces the [coin_catalog] shortcode)
|--------------------------------------------------------------------------
| The home page is served at "/" and at the supported locale paths. The
| catalog is available at "/catalog" and under each locale. The admin panel
| below is not locale-prefixed — it stays in one language for whoever
| manages the site.
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/privacy-policy', [HomeController::class, 'privacyPolicy'])->name('privacy-policy');

Route::get('/catalog', function () {
    return redirect()->route('catalog.index', [
        'locale' => app()->getLocale(),
    ]);
})->name('catalog.page');
Route::get('/coins', function () {
    return redirect()->route('coins.index', [
        'locale' => app()->getLocale(),
    ]);
})->name('coins.page');
Route::get('/coins/{category}', function (string $category) {
    abort_unless(in_array($category, Coin::CATEGORIES, true), 404);

    return redirect()->route('catalog.category', [
        'locale' => app()->getLocale(),
        'category' => $category,
    ]);
})->whereIn('category', Coin::CATEGORIES)->name('coins.category.page');
Route::get('/coin/{coin}', function (Coin $coin) {
    return redirect()->route('catalog.coin', [
        'locale' => app()->getLocale(),
        'coin' => $coin,
    ]);
})->name('coin.page');
Route::get('/artists', function () {
    return redirect()->route('artists.index', [
        'locale' => app()->getLocale(),
    ]);
})->name('artists.page');
Route::get('/artists/{artist}', function (Artist $artist) {
    return redirect()->route('artists.show', [
        'locale' => app()->getLocale(),
        'artist' => $artist,
    ]);
})->name('artists.show.page');
Route::get('/statistics', function () {
    return redirect()->route('catalog.statistics', [
        'locale' => app()->getLocale(),
    ]);
})->name('statistics');

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
    $locale = $request->query('locale', config('app.locale', 'bg'));

    if (in_array($locale, SetLocale::SUPPORTED, true)) {
        App::setLocale($locale);
    }

    return view('graphql-ui');
})->name('graphql.ui');

Route::prefix('{locale}')
    ->whereIn('locale', SetLocale::SUPPORTED)
    ->middleware(SetLocale::class)
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home.locale');
        Route::get('/statistics', [StatisticsController::class, 'index'])->name('catalog.statistics');
        Route::get('/coins', [CoinsController::class, 'index'])->name('coins.index');
        Route::get('/coins/{category}', [CoinsController::class, 'category'])
            ->whereIn('category', Coin::CATEGORIES)
            ->name('catalog.category');
        Route::get('/coin/{coin}', [CoinsController::class, 'show'])->name('catalog.coin');
        Route::get('/artists', [ArtistsController::class, 'index'])->name('artists.index');
        Route::get('/artists/{artist}', [ArtistsController::class, 'show'])->name('artists.show');
        Route::prefix('catalog')->group(function () {
            Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
        });
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
