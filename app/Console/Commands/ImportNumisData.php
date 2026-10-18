<?php

namespace App\Console\Commands;

use App\Models\Coin;
use App\Models\Series;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;

class ImportNumisData extends Command
{
    /**
     * php artisan numis:import [path-to-json]
     * Defaults to database/data/numis_import_data.json
     */
    protected $signature = 'numis:import {path? : Path to the exported JSON file}';

    protected $description = 'Import coins and series from the exported WordPress (Numis plugin) data';

    public function handle(): int
    {
        $pathArgument = $this->argument('path');

        if ($pathArgument !== null && ! is_string($pathArgument)) {
            $this->error('Path must be a string.');

            return self::FAILURE;
        }

        $path = $pathArgument ?? database_path('data/numis_import_data.json');

        if (! file_exists($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        try {
            $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->error('Could not parse JSON file.');

            return self::FAILURE;
        }

        if (! is_array($data)) {
            $this->error('The JSON file must contain an object with series and coins arrays.');

            return self::FAILURE;
        }

        $seriesRows = $data['series'] ?? [];
        $coinRows = $data['coins'] ?? [];

        if (! is_array($seriesRows) || ! is_array($coinRows)) {
            $this->error('The JSON file must contain an object with series and coins arrays.');

            return self::FAILURE;
        }

        $seriesMap = $this->importSeries($seriesRows);
        $this->importCoins($coinRows, $seriesMap);

        $this->info('Import complete.');

        return self::SUCCESS;
    }

    /**
     * @param  array<int, array<string, mixed>>  $seriesRows
     * @return array<int, int> wp term_id => new Series id
     */
    protected function importSeries(array $seriesRows): array
    {
        $map = [];

        // The WordPress export only had English names — stored under the "en" key.
        // Fill in "bg" and "de" from the admin panel afterwards.
        foreach ($seriesRows as $row) {
            $series = Series::updateOrCreate(
                ['slug' => $row['slug']],
                ['name' => ['en' => $row['name']]]
            );
            $map[$row['term_id']] = $series->id;
        }

        $this->info('Series imported: '.count($map));

        return $map;
    }

    /**
     * @param  array<int, array<string, mixed>>  $coinRows
     * @param  array<int, int>  $seriesMap  wp term_id => new Series id
     */
    protected function importCoins(array $coinRows, array $seriesMap): void
    {
        $bar = $this->output->createProgressBar(count($coinRows));
        $bar->start();

        foreach ($coinRows as $row) {
            // A coin now belongs to at most one series. The old export never
            // actually had more than one term per coin, so this is a 1:1 mapping.
            $wpTermId = $row['series_term_ids'][0] ?? null;
            $seriesId = $wpTermId !== null ? ($seriesMap[$wpTermId] ?? null) : null;

            // The WordPress export only had English text — stored under the "en" key.
            // Fill in "bg" and "de" from the admin panel afterwards.
            $coin = Coin::create([
                'title' => ['en' => $row['title']],
                'series_id' => $seriesId,
                'year' => $row['year'] !== null ? (int) $row['year'] : null,
                'denomination' => $row['denomination'],
                'metal' => $row['metal'],
                'diameter' => $row['diameter'],
                'description' => $row['description'] !== null ? ['en' => $row['description']] : null,
                // issue_date, quality, weight, mintage, edge, mint,
                // front_description, back_description did not exist in the old
                // WordPress plugin — they start blank and are filled in from
                // /admin/coins per coin.
            ]);

            $updates = [];
            if (! empty($row['front_image_url'])) {
                $updates['front_image'] = $this->downloadImage($row['front_image_url']);
            }
            if (! empty($row['back_image_url'])) {
                $updates['back_image'] = $this->downloadImage($row['back_image_url']);
            }
            if ($updates) {
                $coin->update($updates);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Coins imported: '.count($coinRows));
    }

    /**
     * Download a remote image (from the old WordPress uploads folder) into
     * storage/app/public/coins and return the relative path, or null on failure.
     */
    protected function downloadImage(string $url): ?string
    {
        try {
            $response = Http::timeout(30)->get($url);

            if (! $response->successful()) {
                $this->warn("  Failed to download ({$response->status()}): {$url}");

                return null;
            }

            $extension = pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $filename = 'coins/'.Str::uuid().'.'.$extension;

            Storage::disk('public')->put($filename, $response->body());

            return $filename;
        } catch (\Throwable $e) {
            $this->warn("  Error downloading {$url}: {$e->getMessage()}");

            return null;
        }
    }
}
