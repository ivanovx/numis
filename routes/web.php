<?php

use App\Http\Controllers\Admin\CoinController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SeriesController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CatalogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public catalog (replaces the [coin_catalog] shortcode)
|--------------------------------------------------------------------------
*/
Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');

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
    Route::resource('coins', CoinController::class);
    Route::resource('series', SeriesController::class);
});
