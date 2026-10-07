<?php

namespace App\Http\Controllers;

use App\Concerns\FiltersCoinsQuery;
use App\Models\Artist;
use App\Models\Coin;
use App\Models\Series;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    use FiltersCoinsQuery;

    public function index(Request $request)
    {
        $coins = $this->filteredQuery($request)->paginate(16)->withQueryString();

        if ($request->input('category') === 'exchange') {
            $coins->setCollection($coins->getCollection()->sort(function (Coin $left, Coin $right): int {
                return $this->exchangeSortValue($right) <=> $this->exchangeSortValue($left);
            }));
        }

        if (in_array($request->input('category'), ['collectible', 'commemorative'], true)) {
            $coins->setCollection($coins->getCollection()->sort(function (Coin $left, Coin $right): int {
                return $this->categorySortValue($left) <=> $this->categorySortValue($right);
            }));
        }

        $data = [
            'coins' => $coins,
            'filters' => $this->currentFilters($request),
            'groupedMetals' => $this->groupedMetals(),
            'diameters' => Coin::whereNotNull('diameter')->distinct()->orderBy('diameter')->pluck('diameter'),
            'denominations' => Coin::whereNotNull('denomination')->distinct()->orderBy('denomination')->pluck('denomination'),
            'allSeries' => Series::forSelect(),
            'allArtists' => Artist::orderBy('name')->get(),
            ...$this->heroData(),
        ];

        // Fetch-based filtering: return just the list fragment for XHR requests.
        if ($request->ajax() || $request->wantsJson()) {
            return view('catalog._list', $data);
        }

        return view('catalog.index', $data);
    }

    public function exportPdf(Request $request)
    {
        $coins = $this->filteredQuery($request)
            ->with(['series', 'artists'])
            ->get();

        $pdf = Pdf::loadView('catalog.pdf', [
            'coins' => $coins,
            'filters' => $this->currentFilters($request),
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('numis-catalog-'.now()->format('Y-m-d').'.pdf');
    }

    protected function groupedMetals()
    {
        return Coin::whereNotNull('metal')
            ->distinct()
            ->pluck('metal')
            ->groupBy(fn (string $metal) => Coin::baseMetal($metal))
            ->map(fn ($group) => $group->sort()->values())
            ->sortKeys();
    }

    protected function heroData(): array
    {
        return [
            'totalCoins' => Coin::count(),
            'earliestYear' => Coin::whereNotNull('year')->min('year'),
            'recentCoins' => Coin::query()
                ->whereNotNull('front_image')
                ->latest('created_at')
                ->take(6)
                ->get(),
        ];
    }

    protected function categorySortValue(Coin $coin): array
    {
        preg_match('/-?\d+(?:[.,]\d+)?/', (string) ($coin->denomination ?? ''), $matches);

        return [
            (float) str_replace(',', '.', $matches[0] ?? '0'),
            (int) ($coin->year ?? 0),
            $coin->series?->name ?? '',
            $coin->id,
        ];
    }

    protected function exchangeSortValue(Coin $coin): array
    {
        preg_match('/-?\d+(?:[.,]\d+)?/', (string) ($coin->denomination ?? ''), $matches);

        return [
            (int) ($coin->year ?? 0),
            (float) str_replace(',', '.', $matches[0] ?? '0'),
            $coin->id,
        ];
    }

    protected function filteredQuery(Request $request)
    {
        $query = Coin::query()->with(['series', 'artists']);

        return $this->applyCoinFilters($query, $this->currentFilters($request));
    }

    protected function currentFilters(Request $request): array
    {
        return $this->coinFiltersFromRequest($request);
    }
}
