<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Models\Artist;
use App\Models\Coin;
use App\Models\Series;

class StatisticsController extends Controller
{
    public function index(string $locale)
    {
        $coins = Coin::query()->get();
        $missingTranslations = $coins->filter(function (Coin $coin): bool {
            return collect(['bg', 'en', 'de'])->contains(
                fn (string $language) => ! filled($coin->translation('title', $language))
            );
        })->count();

        return view('catalog.statistics', [
            'totalCoins' => $coins->count(),
            'totalSeries' => Series::count(),
            'totalArtists' => Artist::count(),
            'artistsWithCoinCounts' => Artist::withCount('coins')->orderBy('name')->get(),
            'coinsByYear' => $coins->groupBy(fn (Coin $coin) => $coin->year ?: 'unknown')->map->count()->sortKeysDesc(),
            'coinsByCategory' => $coins->groupBy('category')->map->count(),
            'missingImages' => $coins->filter(fn (Coin $coin) => ! $coin->front_image || ! $coin->back_image)->count(),
            'missingTranslations' => $missingTranslations,
            'seoTitle' => __('catalog.statistics_title').' | '.__('catalog.site_title'),
            'seoDescription' => __('catalog.statistics_description'),
            'canonicalUrl' => route('catalog.statistics', ['locale' => $locale]),
            'alternateUrls' => collect(SetLocale::SUPPORTED)->mapWithKeys(
                fn (string $supportedLocale) => [$supportedLocale => route('catalog.statistics', ['locale' => $supportedLocale])]
            )->all(),
            'structuredData' => [
                '@context' => 'https://schema.org',
                '@type' => 'Dataset',
                'name' => __('catalog.statistics_title'),
                'description' => __('catalog.statistics_description'),
                'url' => route('catalog.statistics', ['locale' => $locale]),
                'inLanguage' => $locale,
            ],
        ]);
    }
}
