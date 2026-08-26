<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SeriesController extends Controller
{
    public function index()
    {
        // name is a JSON column, so sort by slug instead (name isn't reliably sortable in SQL).
        $series = Series::withCount('coins')->orderBy('slug')->paginate(20)->withQueryString();

        return view('admin.series.index', compact('series'));
    }

    public function create()
    {
        return view('admin.series.create', [
            'series' => new Series(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Series::create($data);

        return redirect()->route('admin.series.index')->with('status', 'Series created.');
    }

    public function edit(Series $series)
    {
        return view('admin.series.edit', compact('series'));
    }

    public function update(Request $request, Series $series)
    {
        $data = $this->validated($request, $series);

        $series->update($data);

        return redirect()->route('admin.series.index')->with('status', 'Series updated.');
    }

    public function destroy(Series $series)
    {
        $series->delete(); // coins.series_id is set to null via the FK (nullOnDelete)

        return redirect()->route('admin.series.index')->with('status', 'Series deleted.');
    }

    protected function validated(Request $request, ?Series $series = null): array
    {
        $id = $series?->id;

        $data = $request->validate([
            'name'    => ['required', 'array'],
            'name.bg' => ['nullable', 'string', 'max:255'],
            'name.en' => ['nullable', 'string', 'max:255'],
            'name.de' => ['nullable', 'string', 'max:255'],
            'slug'    => ['nullable', 'string', 'max:255', 'unique:series,slug,' . $id],
        ]);

        $firstName = collect($data['name'])->first(fn ($v) => filled($v));

        if (! $firstName) {
            throw ValidationException::withMessages([
                'name.en' => 'Please provide a name in at least one language.',
            ]);
        }

        $data['slug'] = $data['slug'] ? Str::slug($data['slug']) : Str::slug($firstName);

        return $data;
    }
}
