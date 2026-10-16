<?php

namespace App\Http\Controllers;

use App\Concerns\FiltersCoinsQuery;
use App\Http\Middleware\SetLocale;
use App\Models\Coin;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CoinsController extends Controller
{
    use FiltersCoinsQuery;

    public function index(): View
    {
        return view('coins.index', [
            'categories' => Coin::CATEGORIES,
        ]);
    }

    public function category(string $locale, string $category): View
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

    public function show(string $locale, Coin $coin, Request $request): View
    {
        $coin->load(['series', 'artists']);

        $description = collect([
            $coin->description,
            $coin->front_description,
            $coin->back_description,
        ])->filter()->implode(' ');

        $contextFilters = $this->coinFiltersFromRequest($request);

        return view('catalog.show', [
            'coin' => $coin,
            'seoTitle' => $coin->title.' | '.__('catalog.site_title'),
            'seoDescription' => Str::limit(trim(strip_tags($description)), 160),
            'canonicalUrl' => route('catalog.coin', ['locale' => $locale, 'coin' => $coin]),
            'alternateUrls' => collect(SetLocale::SUPPORTED)->mapWithKeys(
                fn (string $supportedLocale) => [$supportedLocale => route('catalog.coin', ['locale' => $supportedLocale, 'coin' => $coin])]
            )->all(),
            'ogImage' => $coin->front_image_url,
            'similarCoins' => $this->similarCoins($coin),
            'seriesTimeline' => $this->seriesTimeline($coin),
            'contextFilters' => $contextFilters,
            'neighborCoins' => $this->neighborCoins($coin, $contextFilters),
            'breadcrumbItems' => $this->coinBreadcrumbItems($coin),
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
            ] + $this->referenceStructuredData($coin, $locale),
        ]);
    }

    /**
     * schema.org identifiers for the catalog numbers (KM#, Numista, BNB code…)
     * and sameAs links to the same coin on other sites, so search engines can
     * connect the page with those catalogs.
     */
    /** @return array<string, mixed> */
    protected function referenceStructuredData(Coin $coin, string $locale): array
    {
        $references = collect($coin->catalogReferences($locale));

        $identifiers = $references->map(fn (array $reference) => [
            '@type' => 'PropertyValue',
            'propertyID' => match ($reference['key']) {
                'km' => 'Krause-Mishler KM#',
                'numista' => 'Numista',
                'bnb' => 'BNB emission code',
                default => 'Catalog reference',
            },
            'value' => $reference['value'],
        ])->values()->all();

        $sameAs = $references->pluck('url')->filter()->values()->all();

        return ($identifiers ? ['identifier' => $identifiers] : [])
            + ($sameAs ? ['sameAs' => $sameAs] : []);
    }

    /**
     * Find the previous/next coin within the same filtered, ordered result
     * set the user was browsing before opening this coin. $filters comes
     * from the query string that catalog._card appends to every coin link,
     * so the "current list" context survives the click through.
     */
    /**
     * @param  array<string, mixed>  $filters
     * @return array{prev: ?Coin, next: ?Coin}
     */
    protected function neighborCoins(Coin $coin, array $filters): array
    {
        $ids = $this->applyCoinFilters(Coin::query(), $filters)->pluck('id')->values();
        $position = $ids->search($coin->id);

        if (! is_int($position)) {
            return ['prev' => null, 'next' => null];
        }

        return [
            'prev' => $position > 0 ? Coin::query()->find((int) $ids[$position - 1]) : null,
            'next' => $position < $ids->count() - 1 ? Coin::query()->find((int) $ids[$position + 1]) : null,
        ];
    }

    /** @return list<array{label: string, url?: string}> */
    protected function coinBreadcrumbItems(Coin $coin): array
    {
        $items = [];

        if ($coin->category) {
            $items[] = [
                'label' => (string) __('catalog.categories.'.$coin->category),
                'url' => route('catalog.category', ['locale' => app()->getLocale(), 'category' => $coin->category]),
            ];
        }

        $items[] = ['label' => (string) $coin->title];

        return $items;
    }

    /** @return Collection<int, Coin> */
    protected function seriesTimeline(Coin $coin): Collection
    {
        if (! $coin->series_id) {
            return collect();
        }

        return Coin::query()
            ->where('series_id', $coin->series_id)
            ->orderBy('year')
            ->orderBy('id')
            ->get(['id', 'title', 'year', 'front_image']);
    }

    /** @return EloquentCollection<int, Coin> */
    protected function similarCoins(Coin $coin): EloquentCollection
    {
        $artistIds = $coin->artists->pluck('id');

        return Coin::query()
            ->with(['series', 'artists'])
            ->where('id', '!=', $coin->id)
            ->where(function ($query) use ($coin, $artistIds) {
                if ($coin->series_id) {
                    $query->orWhere('series_id', $coin->series_id);
                }

                if ($artistIds->isNotEmpty()) {
                    $query->orWhereHas('artists', fn ($q) => $q->whereIn('artists.id', $artistIds));
                }

                if (! $coin->series_id && $artistIds->isEmpty()) {
                    $query->orWhere('category', $coin->category);
                }
            })
            ->inRandomOrder()
            ->take(4)
            ->get();
    }

    /**
     * @param  Collection<int, Coin>  $coins
     * @return Collection<int|string, Collection<int, Coin>>
     */
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
        return $coin->series->name ?? __('catalog.no_series');
    }
}
