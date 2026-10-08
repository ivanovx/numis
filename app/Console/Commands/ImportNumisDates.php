<?php

namespace App\Console\Commands;

use App\Models\Coin;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportNumisDates extends Command
{
    /**
     * php artisan numis:import_dates [path-to-json] [--apply] [--force]
     * Defaults to database/data/numis_dates_import.json
     *
     * By default this runs as a DRY RUN: it only prints what it would do.
     * Pass --apply to actually write the issue_date column.
     * Pass --force to overwrite coins that already have an issue_date set
     * (without it, coins with an existing issue_date are left untouched).
     */
    protected $signature = 'numis:import_dates
        {path? : Path to the dates JSON file}
        {--apply : Actually write changes (default is a dry run)}
        {--force : Overwrite coins that already have an issue_date}';

    protected $description = 'Match coins by title/year and fill in their issue_date from researched BNB circulation dates';

    /** Minimum normalized-title similarity (0-100) to accept a fuzzy match. */
    protected const MIN_SIMILARITY = 55;

    public function handle(): int
    {
        $path = $this->argument('path') ?? database_path('data/numis_dates_import.json');

        if (! file_exists($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $rows = json_decode(file_get_contents($path), true);

        if (! is_array($rows)) {
            $this->error('Could not parse JSON file.');

            return self::FAILURE;
        }

        $apply = (bool) $this->option('apply');
        $force = (bool) $this->option('force');

        if (! $apply) {
            $this->warn('Dry run — no changes will be saved. Pass --apply to write them.');
        }

        $matched = 0;
        $skippedExisting = 0;
        $notFound = [];
        $ambiguous = [];

        foreach ($rows as $row) {
            $year = $row['year'] ?? null;
            $title = trim((string) ($row['title'] ?? ''));
            $denomination = $row['denomination'] ?? null;
            $date = $row['date'] ?? null;

            if (! $year || ! $title || ! $date) {
                continue;
            }

            $candidates = Coin::query()->where('year', $year);

            if ($denomination) {
                $candidates = Coin::query()->where('year', $year)->where('denomination', $denomination);

                // If the denomination filter happens to match nothing (naming
                // differs), fall back to year-only so we still get a chance
                // to fuzzy-match on the title.
                if ($candidates->count() === 0) {
                    $candidates = Coin::query()->where('year', $year);
                }
            }

            $pool = $candidates->get();
            $scored = $pool->map(fn (Coin $coin) => [
                'coin' => $coin,
                'score' => $this->bestTitleSimilarity($coin, $title),
            ])->filter(fn ($row) => $row['score'] >= self::MIN_SIMILARITY)
                ->sortByDesc('score')
                ->values();

            if ($scored->isEmpty()) {
                $notFound[] = "{$year} — {$title}".($denomination ? " ({$denomination})" : '');

                continue;
            }

            // If the top match isn't clearly better than the runner-up,
            // don't guess — flag it for manual review instead.
            if ($scored->count() > 1 && ($scored[0]['score'] - $scored[1]['score']) < 10) {
                $names = $scored->take(3)->map(fn ($r) => $r['coin']->title.' (#'.$r['coin']->id.', '.round($r['score']).'%)')->implode(' | ');
                $ambiguous[] = "{$year} — {$title}: {$names}";

                continue;
            }

            /** @var Coin $coin */
            $coin = $scored[0]['coin'];

            if ($coin->issue_date && ! $force) {
                $skippedExisting++;

                continue;
            }

            $this->line(sprintf(
                '  [%s] #%d %s  →  %s (match %d%%)',
                $apply ? 'SET' : 'WOULD SET',
                $coin->id,
                $coin->title,
                $date,
                round($scored[0]['score'])
            ));

            if ($apply) {
                $coin->issue_date = $date;
                $coin->save();
            }

            $matched++;
        }

        $this->newLine();
        $this->info("Matched: {$matched}".($apply ? ' (saved)' : ' (dry run — not saved)'));
        $this->info("Skipped (already had issue_date, use --force to overwrite): {$skippedExisting}");

        if ($notFound) {
            $this->newLine();
            $this->warn('Not found ('.count($notFound).'):');
            foreach ($notFound as $line) {
                $this->line("  - {$line}");
            }
        }

        if ($ambiguous) {
            $this->newLine();
            $this->warn('Ambiguous — needs manual review ('.count($ambiguous).'):');
            foreach ($ambiguous as $line) {
                $this->line("  - {$line}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * Compare the import title against the coin's title in every available
     * locale and return the best similarity score (0-100).
     */
    protected function bestTitleSimilarity(Coin $coin, string $importTitle): float
    {
        $raw = $coin->getRawOriginal('title');
        $translations = json_decode((string) $raw, true) ?: [];

        // Always also compare against the locale-resolved accessor, in case
        // the raw column isn't JSON (plain string fallback).
        $translations[] = $coin->title;

        $needle = $this->normalize($importTitle);
        $best = 0.0;

        foreach (array_filter($translations) as $candidate) {
            $hay = $this->normalize((string) $candidate);
            if ($hay === '' || $needle === '') {
                continue;
            }

            similar_text($needle, $hay, $percent);

            // Bonus: if one fully contains the other (common when the coin
            // title has extra prefix like "Българско възраждане • "), treat
            // it as a strong match regardless of overall string length.
            if (Str::contains($hay, $needle) || Str::contains($needle, $hay)) {
                $percent = max($percent, 85.0);
            }

            $best = max($best, $percent);
        }

        return $best;
    }

    protected function normalize(string $value): string
    {
        $value = mb_strtolower($value, 'UTF-8');
        $value = str_replace(['"', '„', '“', '»', '«', '.', ',', '-', '–', '•'], ' ', $value);

        return trim(preg_replace('/\s+/u', ' ', $value));
    }
}
