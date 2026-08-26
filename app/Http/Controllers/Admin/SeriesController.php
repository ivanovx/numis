<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SeriesController extends Controller
{
    public function index()
    {
        $series = Series::with('parent')->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.series.index', compact('series'));
    }

    public function create()
    {
        return view('admin.series.create', [
            'series'    => new Series(),
            'allSeries' => Series::flatTree(),
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
        return view('admin.series.edit', [
            'series'    => $series,
            'allSeries' => Series::flatTree()->reject(fn ($s) => $s->id === $series->id),
        ]);
    }

    public function update(Request $request, Series $series)
    {
        $data = $this->validated($request, $series);

        if ((int) ($data['parent_id'] ?? 0) === $series->id) {
            return back()->withErrors(['parent_id' => 'A series cannot be its own parent.'])->withInput();
        }

        $series->update($data);

        return redirect()->route('admin.series.index')->with('status', 'Series updated.');
    }

    public function destroy(Series $series)
    {
        $series->delete();

        return redirect()->route('admin.series.index')->with('status', 'Series deleted.');
    }

    protected function validated(Request $request, ?Series $series = null): array
    {
        $id = $series?->id;

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'slug'      => ['nullable', 'string', 'max:255', 'unique:series,slug,' . $id],
            'parent_id' => ['nullable', 'exists:series,id'],
        ]);

        $data['slug'] = $data['slug'] ? Str::slug($data['slug']) : Str::slug($data['name']);
        $data['parent_id'] = $data['parent_id'] ?: null;

        return $data;
    }
}
