<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use App\Models\Series;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $coins = $this->filteredQuery($request)->paginate(16)->withQueryString();

        $data = [
            'coins'      => $coins,
            'filters'    => $this->currentFilters($request),
            'metals'     => Coin::whereNotNull('metal')->distinct()->orderBy('metal')->pluck('metal'),
            'diameters'  => Coin::whereNotNull('diameter')->distinct()->orderBy('diameter')->pluck('diameter'),
            'allSeries'  => Series::flatTree(),
        ];

        // Fetch-based filtering: return just the list fragment for XHR requests.
        if ($request->ajax() || $request->wantsJson()) {
            return view('catalog._list', $data);
        }

        return view('catalog.index', $data);
    }

    protected function filteredQuery(Request $request)
    {
        $query = Coin::query()->with('series');

        if ($request->filled('year_from') || $request->filled('year_to')) {
            $query->whereBetween('year', [
                (int) $request->input('year_from', 0),
                (int) $request->input('year_to', 9999),
            ]);
        }

        foreach (['metal', 'diameter'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->input('series') === 'none') {
            $query->whereDoesntHave('series');
        } elseif ($request->filled('series')) {
            $query->whereHas('series', fn ($q) => $q->where('slug', $request->input('series')));
        }

        return $query->orderByDesc('year');
    }

    protected function currentFilters(Request $request): array
    {
        return [
            'year_from' => $request->input('year_from', ''),
            'year_to'   => $request->input('year_to', ''),
            'metal'     => $request->input('metal', ''),
            'diameter'  => $request->input('diameter', ''),
            'series'    => $request->input('series', ''),
        ];
    }
}
