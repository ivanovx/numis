<?php

namespace App\Console\Commands;

use App\Models\Coin;
use App\Models\Series;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $path = $this->argument('path') ?? database_path('data/numis_import_data.json');

        if (! file_exists($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $data = json_decode(file_get_contents($path), true);

        $seriesMap = $this->importSeries($data['series'] ?? []);
        $this->importCoins($data['coins'] ?? [], $seriesMap);

        $this->info('Import complete.');
        return self::SUCCESS;
    }

    /**
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

        $this->info('Series imported: ' . count($map));

        return $map;
    }

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
                'title'        => ['en' => $row['title']],
                'series_id'    => $seriesId,
                'year'         => $row['year'] !== null ? (int) $row['year'] : null,
                'denomination' => $row['denomination'],
                'metal'        => $row['metal'],
                'diameter'     => $row['diameter'],
                'description'  => $row['description'] !== null ? ['en' => $row['description']] : null,
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
        $this->info('Coins imported: ' . count($coinRows));
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

            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $filename  = 'coins/' . Str::uuid() . '.' . $extension;

            Storage::disk('public')->put($filename, $response->body());

            return $filename;
        } catch (\Throwable $e) {
            $this->warn("  Error downloading {$url}: {$e->getMessage()}");
            return null;
        }
    }
}
