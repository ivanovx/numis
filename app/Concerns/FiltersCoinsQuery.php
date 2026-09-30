<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FiltersCoinsQuery
{
    /**
     * Apply the same set of catalog filters used on the main listing to any
     * Coin query builder. Used both for the listing itself and for building
     * the prev/next neighbor lookup on the coin detail page, so the two
     * always stay in sync.
     */
    protected function applyCoinFilters(Builder $query, array $filters): Builder
    {
        $yearFrom = $filters['year_from'] ?? '';
        $yearTo = $filters['year_to'] ?? '';

        if ($yearFrom !== '' || $yearTo !== '') {
            $query->whereBetween('year', [
                (int) ($yearFrom !== '' ? $yearFrom : 0),
                (int) ($yearTo !== '' ? $yearTo : 9999),
            ]);
        }

        foreach (['category', 'diameter', 'denomination'] as $field) {
            if (($filters[$field] ?? '') !== '') {
                $query->where($field, $filters[$field]);
            }
        }

        if (($filters['metal'] ?? '') !== '') {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $filters['metal']);
            $query->where('metal', 'LIKE', $escaped.'%');
        }

        if (($filters['series'] ?? '') === 'none') {
            $query->whereNull('series_id');
        } elseif (($filters['series'] ?? '') !== '') {
            $query->whereHas('series', fn ($q) => $q->where('slug', $filters['series']));
        }

        if (($filters['artist'] ?? '') !== '') {
            $query->whereHas('artists', fn ($q) => $q->where('artists.slug', $filters['artist']));
        }

        // orderByDesc('id') is a deterministic tiebreaker so prev/next never
        // "skips" or repeats a coin when several share the same year.
        return $query->orderByDesc('year')->orderByDesc('id');
    }

    protected function coinFiltersFromRequest(Request $request): array
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
