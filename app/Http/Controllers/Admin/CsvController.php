<?php

namespace App\Http\Controllers\Admin;

use App\Models\Artist;
use App\Models\Coin;
use App\Models\Series;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvController
{
    private const RESOURCES = ['coins', 'series', 'artists'];

    public function export(string $resource): StreamedResponse
    {
        abort_unless(in_array($resource, self::RESOURCES, true), 404);

        $filename = 'numis-'.$resource.'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($resource): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");

            if ($resource === 'coins') {
                $this->exportCoins($output);
            } elseif ($resource === 'series') {
                $this->exportSeries($output);
            } else {
                $this->exportArtists($output);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request, string $resource): RedirectResponse
    {
        abort_unless(in_array($resource, self::RESOURCES, true), 404);

        $request->validate(['csv' => ['required', 'file', 'mimes:csv,txt', 'max:10240']]);

        try {
            $result = DB::transaction(fn (): array => match ($resource) {
                'coins' => $this->importCoins($request->file('csv')),
                'series' => $this->importSeries($request->file('csv')),
                'artists' => $this->importArtists($request->file('csv')),
            });
        } catch (\Throwable $exception) {
            return back()->withErrors(['csv' => 'Import failed: '.$exception->getMessage()]);
        }

        $message = sprintf('%d %s imported.', $result['imported'], $resource);

        if ($result['errors'] !== []) {
            $message .= ' Skipped rows: '.implode(' | ', $result['errors']);
        }

        return back()->with('status', $message);
    }

    private function exportCoins($output): void
    {
        $headers = ['id', 'title_bg', 'title_en', 'title_de', 'series_slug', 'artist_slugs', 'category', 'year', 'issue_date', 'denomination', 'metal', 'quality', 'weight', 'diameter', 'mintage', 'edge_bg', 'edge_en', 'edge_de', 'mint_bg', 'mint_en', 'mint_de', 'front_image', 'front_description_bg', 'front_description_en', 'front_description_de', 'back_image', 'back_description_bg', 'back_description_en', 'back_description_de', 'description_bg', 'description_en', 'description_de'];
        fputcsv($output, $headers);

        Coin::with(['series', 'artists'])->orderBy('id')->chunkById(200, function ($coins) use ($output, $headers): void {
            foreach ($coins as $coin) {
                $row = ['id' => $coin->id, 'title_bg' => $coin->translation('title', 'bg'), 'title_en' => $coin->translation('title', 'en'), 'title_de' => $coin->translation('title', 'de'), 'series_slug' => $coin->series?->slug, 'artist_slugs' => $coin->artists->pluck('slug')->implode('|'), 'category' => $coin->category, 'year' => $coin->year, 'issue_date' => $coin->issue_date?->format('Y-m-d'), 'denomination' => $coin->denomination, 'metal' => $coin->metal, 'quality' => $coin->quality, 'weight' => $coin->weight, 'diameter' => $coin->diameter, 'mintage' => $coin->mintage, 'edge_bg' => $coin->translation('edge', 'bg'), 'edge_en' => $coin->translation('edge', 'en'), 'edge_de' => $coin->translation('edge', 'de'), 'mint_bg' => $coin->translation('mint', 'bg'), 'mint_en' => $coin->translation('mint', 'en'), 'mint_de' => $coin->translation('mint', 'de'), 'front_image' => $coin->front_image, 'front_description_bg' => $coin->translation('front_description', 'bg'), 'front_description_en' => $coin->translation('front_description', 'en'), 'front_description_de' => $coin->translation('front_description', 'de'), 'back_image' => $coin->back_image, 'back_description_bg' => $coin->translation('back_description', 'bg'), 'back_description_en' => $coin->translation('back_description', 'en'), 'back_description_de' => $coin->translation('back_description', 'de'), 'description_bg' => $coin->translation('description', 'bg'), 'description_en' => $coin->translation('description', 'en'), 'description_de' => $coin->translation('description', 'de')];
                fputcsv($output, array_map(fn ($header) => $row[$header] ?? '', $headers));
            }
        });
    }

    private function exportSeries($output): void
    {
        $headers = ['id', 'name_bg', 'name_en', 'name_de', 'slug'];
        fputcsv($output, $headers);
        Series::orderBy('id')->chunkById(200, function ($items) use ($output): void {
            foreach ($items as $item) {
                fputcsv($output, [$item->id, $item->translation('name', 'bg'), $item->translation('name', 'en'), $item->translation('name', 'de'), $item->slug]);
            }
        });
    }

    private function exportArtists($output): void
    {
        fputcsv($output, ['id', 'name', 'slug']);
        Artist::orderBy('id')->chunkById(200, function ($items) use ($output): void {
            foreach ($items as $item) {
                fputcsv($output, [$item->id, $item->name, $item->slug]);
            }
        });
    }

    private function importSeries(UploadedFile $file): array
    {
        $rows = $this->rows($file);
        $count = 0;
        $errors = [];

        foreach ($rows as $line => $row) {
            $slug = Str::slug($row['slug'] ?? $row['name_bg'] ?? '');
            $name = collect([$row['name_bg'] ?? '', $row['name_en'] ?? '', $row['name_de'] ?? ''])->first(fn ($value) => filled(trim($value)));
            if ($slug === '' || ! filled($name)) {
                $errors[] = (string) $line;

                continue;
            }
            Series::updateOrCreate(['slug' => $slug], ['name' => ['bg' => $row['name_bg'] ?? '', 'en' => $row['name_en'] ?? '', 'de' => $row['name_de'] ?? '']]);
            $count++;
        }

        return compact('count', 'errors') + ['imported' => $count];
    }

    private function importArtists(UploadedFile $file): array
    {
        $count = 0;
        $errors = [];
        foreach ($this->rows($file) as $line => $row) {
            $name = trim($row['name'] ?? '');
            $slug = Str::slug($row['slug'] ?? $name);
            if ($name === '' || $slug === '') {
                $errors[] = (string) $line;

                continue;
            }
            Artist::updateOrCreate(['slug' => $slug], ['name' => $name]);
            $count++;
        }

        return compact('count', 'errors') + ['imported' => $count];
    }

    private function importCoins(UploadedFile $file): array
    {
        $count = 0;
        $errors = [];
        foreach ($this->rows($file) as $line => $row) {
            $title = ['bg' => trim($row['title_bg'] ?? ''), 'en' => trim($row['title_en'] ?? ''), 'de' => trim($row['title_de'] ?? '')];
            if ($title['bg'] === '' && $title['en'] === '' && $title['de'] === '') {
                $errors[] = (string) $line;

                continue;
            }
            $series = ($row['series_slug'] ?? '') !== '' ? Series::where('slug', $row['series_slug'])->first() : null;
            $coin = Coin::updateOrCreate(['id' => (int) ($row['id'] ?? 0) ?: null], ['title' => $title, 'series_id' => $series?->id, 'category' => $row['category'] ?: Coin::CATEGORIES[0], 'year' => $row['year'] ?: null, 'issue_date' => $row['issue_date'] ?: null, 'denomination' => $row['denomination'] ?: null, 'metal' => $row['metal'] ?: null, 'quality' => $row['quality'] ?: null, 'weight' => $row['weight'] ?: null, 'diameter' => $row['diameter'] ?: null, 'mintage' => $row['mintage'] ?: null, 'edge' => ['bg' => $row['edge_bg'] ?? '', 'en' => $row['edge_en'] ?? '', 'de' => $row['edge_de'] ?? ''], 'mint' => ['bg' => $row['mint_bg'] ?? '', 'en' => $row['mint_en'] ?? '', 'de' => $row['mint_de'] ?? ''], 'front_image' => $row['front_image'] ?: null, 'front_description' => ['bg' => $row['front_description_bg'] ?? '', 'en' => $row['front_description_en'] ?? '', 'de' => $row['front_description_de'] ?? ''], 'back_image' => $row['back_image'] ?: null, 'back_description' => ['bg' => $row['back_description_bg'] ?? '', 'en' => $row['back_description_en'] ?? '', 'de' => $row['back_description_de'] ?? ''], 'description' => ['bg' => $row['description_bg'] ?? '', 'en' => $row['description_en'] ?? '', 'de' => $row['description_de'] ?? '']]);
            $artistIds = collect(explode('|', $row['artist_slugs'] ?? ''))->filter()->map(fn ($slug) => Artist::where('slug', $slug)->value('id'));
            $coin->artists()->sync($artistIds->filter()->all());
            $count++;
        }

        return compact('count', 'errors') + ['imported' => $count];
    }

    private function rows(UploadedFile $file): \Generator
    {
        $handle = fopen($file->getRealPath(), 'r');
        $headers = array_map(fn ($header) => trim((string) $header), fgetcsv($handle));
        $line = 1;
        while (($values = fgetcsv($handle)) !== false) {
            $line++;
            yield $line => array_combine($headers, array_pad($values, count($headers), ''));
        }
        fclose($handle);
    }
}
