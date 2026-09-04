<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Models\Artist;
use App\Models\Coin;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $coins = $this->filteredQuery($request)->paginate(16)->withQueryString();

        $data = [
            'coins' => $coins,
            'filters' => $this->currentFilters($request),
            'metals' => Coin::whereNotNull('metal')->distinct()->orderBy('metal')->pluck('metal'),
            'diameters' => Coin::whereNotNull('diameter')->distinct()->orderBy('diameter')->pluck('diameter'),
            'denominations' => Coin::whereNotNull('denomination')->distinct()->orderBy('denomination')->pluck('denomination'),
            'allSeries' => Series::forSelect(),
            'allArtists' => Artist::orderBy('name')->get(),
        ];

        // Fetch-based filtering: return just the list fragment for XHR requests.
        if ($request->ajax() || $request->wantsJson()) {
            return view('catalog._list', $data);
        }

        return view('catalog.index', $data);
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

    protected function filteredQuery(Request $request)
    {
        $query = Coin::query()->with(['series', 'artists']);

        // Year range — a single year works too (year_from == year_to).
        if ($request->filled('year_from') || $request->filled('year_to')) {
            $query->whereBetween('year', [
                (int) $request->input('year_from', 0),
                (int) $request->input('year_to', 9999),
            ]);
        }

        foreach (['category', 'metal', 'diameter', 'denomination'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->input('series') === 'none') {
            $query->whereNull('series_id');
        } elseif ($request->filled('series')) {
            $query->whereHas('series', fn ($q) => $q->where('slug', $request->input('series')));
        }

        if ($request->filled('artist')) {
            $query->whereHas('artists', fn ($q) => $q->where('artists.slug', $request->input('artist')));
        }

        return $query->orderByDesc('year');
    }

    protected function currentFilters(Request $request): array
    {
        return [
            'year_from' => $request->input('year_from', ''),
            'year_to' => $request->input('year_to', ''),
            'category' => $request->input('category', ''),
            'metal' => $request->input('metal', ''),
            'diameter' => $request->input('diameter', ''),
            'denomination' => $request->input('denomination', ''),
            'series' => $request->input('series', ''),
            'artist' => $request->input('artist', ''),
        ];
    }
}
