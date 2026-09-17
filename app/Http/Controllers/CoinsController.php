<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Models\Coin;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CoinsController extends Controller
{
    public function index()
    {
        return view('coins.index', [
            'categories' => Coin::CATEGORIES,
        ]);
    }

    public function category(string $locale, string $category)
    {
        abort_unless(in_array($category, Coin::CATEGORIES, true), 404);

        $coins = Coin::query()
            ->with(['series', 'artists'])
            ->get();

        if ($category === 'exchange') {
            return view('coins.categories.exchange', [
                'coinsByYear' => $this->exchangeCoinsByYear($coins),
            ]);
        }

        $categoryCoins = $coins->where('category', $category);

        return view("coins.categories.{$category}", [
            'category' => $category,
            'coins' => $categoryCoins,
            'seriesGroups' => in_array($category, ['collectible', 'commemorative'], true)
                ? $categoryCoins
                    ->sortBy(fn (Coin $coin) => [$this->seriesName($coin), $this->denominationValue($coin), (int) ($coin->year ?? 0)])
                    ->groupBy(fn (Coin $coin) => $this->seriesName($coin))
                : null,
        ]);
    }

    public function show(string $locale, Coin $coin)
    {
        $coin->load(['series', 'artists']);

        $description = collect([
            $coin->description,
            $coin->front_description,
            $coin->back_description,
        ])->filter()->implode(' ');

        return view('catalog.show', [
            'coin' => $coin,
            'seoTitle' => $coin->title.' | '.__('catalog.site_title'),
            'seoDescription' => Str::limit(trim(strip_tags($description)), 160),
            'canonicalUrl' => route('catalog.coin', ['locale' => $locale, 'coin' => $coin]),
            'alternateUrls' => collect(SetLocale::SUPPORTED)->mapWithKeys(
                fn (string $supportedLocale) => [$supportedLocale => route('catalog.coin', ['locale' => $supportedLocale, 'coin' => $coin])]
            )->all(),
            'ogImage' => $coin->front_image_url,
            'structuredData' => [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $coin->title,
                'description' => Str::limit(trim(strip_tags($description)), 300),
                'url' => route('catalog.coin', ['locale' => $locale, 'coin' => $coin]),
                'image' => array_values(array_filter([$coin->front_image_url, $coin->back_image_url])),
                'category' => $coin->category,
                'brand' => [
                    '@type' => 'Brand',
                    'name' => __('catalog.site_title'),
                ],
            ],
        ]);
    }

    protected function exchangeCoinsByYear(Collection $coins): Collection
    {
        return $coins
            ->where('category', 'exchange')
            ->sort(function (Coin $left, Coin $right): int {
                $yearComparison = (int) ($right->year ?? 0) <=> (int) ($left->year ?? 0);

                if ($yearComparison !== 0) {
                    return $yearComparison;
                }

                return $this->denominationValue($left) <=> $this->denominationValue($right);
            })
            ->groupBy(fn (Coin $coin) => $coin->year ?: 'unknown');
    }

    protected function denominationValue(Coin $coin): float
    {
        preg_match('/-?\d+(?:[.,]\d+)?/', (string) ($coin->denomination ?? ''), $matches);

        return (float) str_replace(',', '.', $matches[0] ?? '0');
    }

    protected function seriesName(Coin $coin): string
    {
        return $coin->series?->name ?? __('catalog.no_series');
    }
}
