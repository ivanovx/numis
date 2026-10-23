<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Series;
use App\Services\CoinTranslationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class SeriesController extends Controller
{
    public function index(): View
    {
        return view('admin.series.index');
    }

    public function create(): View
    {
        return view('admin.series.create', [
            'series' => new Series,
        ]);
    }

    public function store(Request $request, CoinTranslationService $translator): RedirectResponse
    {
        $data = $this->validated($request);

        try {
            $data = $translator->translateFields($data, ['name']);
        } catch (Throwable $exception) {
            throw ValidationException::withMessages([
                'name.bg' => 'Автоматичният превод не бе успешен: '.$exception->getMessage(),
            ]);
        }

        Series::create($data);

        return redirect()->route('admin.series.index')->with('status', 'Серията е добавена.');
    }

    public function edit(Series $series): View
    {
        return view('admin.series.edit', compact('series'));
    }

    public function update(Request $request, Series $series): RedirectResponse
    {
        $data = $this->validated($request, $series);

        $series->update($data);

        return redirect()->route('admin.series.index')->with('status', 'Серията е редактирана.');
    }

    public function destroy(Series $series): RedirectResponse
    {
        $series->delete(); // coins.series_id is set to null via the FK (nullOnDelete)

        return redirect()->route('admin.series.index')->with('status', 'Серията е изтрита.');
    }

    /** @return array<string, mixed> */
    protected function validated(Request $request, ?Series $series = null): array
    {
        $id = $series?->id;

        $data = $request->validate([
            'name' => ['required', 'array'],
            'name.bg' => ['nullable', 'string', 'max:255'],
            'name.en' => ['nullable', 'string', 'max:255'],
            'name.de' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:series,slug,'.$id],
        ]);

        $firstName = collect((array) $data['name'])->first(fn ($v) => filled($v));

        if (! $firstName) {
            throw ValidationException::withMessages([
                'name.en' => 'Въведи име поне на един език.',
            ]);
        }

        $data['slug'] = $data['slug'] ? Str::slug($data['slug']) : Str::slug($firstName);

        return $data;
    }
}
