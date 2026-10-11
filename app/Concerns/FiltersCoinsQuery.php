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
        if (($filters['q'] ?? '') !== '') {
            $this->applyTextSearch($query, trim($filters['q']));
        }

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
            // Prefix match: picking the base metal ("Ag 925/1000") also finds its variants.
            $lower = $this->lowerFunction($query);
            $query->whereRaw("{$lower}(coins.metal) LIKE ? ESCAPE '!'", [$this->escapeLike(mb_strtolower($filters['metal'])).'%']);
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

    /**
     * Free-text search: titles, descriptions, artist, series, and catalog
     * numbers (KM#, Numista, BNB code, other references).
     */
    protected function applyTextSearch(Builder $query, string $term): void
    {
        $lower = $this->lowerFunction($query);
        $like = '%'.$this->escapeLike(mb_strtolower($term)).'%';

        // "KM# 202", "KM 202", "N#123" -> search the bare number, as a prefix
        // ("202" finds KM# 202 and 202.1) so collectors can paste a reference as-is.
        $bare = trim((string) preg_replace('/^\s*(?:KM|Numista|N)\s*#?\s*/i', '', $term));
        $referencePrefix = $bare !== '' ? $this->escapeLike(mb_strtolower($bare)).'%' : null;

        $query->where(function (Builder $q) use ($lower, $like, $referencePrefix) {
            foreach (['title', 'description', 'front_description', 'back_description', 'bnb_number', 'other_references'] as $column) {
                $q->orWhereRaw("{$lower}(coins.{$column}) LIKE ? ESCAPE '!'", [$like]);
            }

            if ($referencePrefix !== null) {
                foreach (['km_number', 'numista_number'] as $column) {
                    $q->orWhereRaw("{$lower}(coins.{$column}) LIKE ? ESCAPE '!'", [$referencePrefix]);
                }
            }

            $q->orWhereHas('artists', fn ($a) => $a->whereRaw("{$lower}(artists.name) LIKE ? ESCAPE '!'", [$like]));
            $q->orWhereHas('series', fn ($s) => $s->whereRaw("{$lower}(series.name) LIKE ? ESCAPE '!'", [$like]));
        });
    }

    /**
     * Name of a SQL function that lower-cases Unicode text.
     *
     * SQLite's built-in LOWER()/LIKE only fold ASCII, so a search for "ботев"
     * would never match "Ботев". On SQLite we register a Unicode-aware
     * function on the connection; MySQL/MariaDB/PostgreSQL already handle it.
     */
    protected function lowerFunction(Builder $query): string
    {
        $connection = $query->getModel()->getConnection();

        if ($connection->getDriverName() !== 'sqlite') {
            return 'LOWER';
        }

        $pdo = $connection->getPdo();
        $callback = fn ($value) => $value === null ? null : mb_strtolower((string) $value);

        if (method_exists($pdo, 'sqliteCreateFunction')) {
            $pdo->sqliteCreateFunction('mb_lower', $callback, 1);
        } else {
            $pdo->createFunction('mb_lower', $callback, 1);
        }

        return 'mb_lower';
    }

    /** Escape LIKE wildcards using "!" — works the same on SQLite and MySQL (a backslash does not on SQLite). */
    protected function escapeLike(string $value): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);
    }

    protected function coinFiltersFromRequest(Request $request): array
    {
        return [
            'q' => trim((string) $request->input('q', '')),
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
