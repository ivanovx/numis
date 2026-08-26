<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Coin;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CoinController extends Controller
{
    public function index()
    {
        // title is a JSON column, so sort by year instead (title isn't reliably sortable in SQL).
        $coins = Coin::with(['series', 'artists'])->orderByDesc('year')->paginate(20)->withQueryString();

        return view('admin.coins.index', compact('coins'));
    }

    public function create()
    {
        return view('admin.coins.create', [
            'coin'       => new Coin(),
            'allSeries'  => Series::forSelect(),
            'allArtists' => Artist::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $coin = Coin::create($data);

        $coin->artists()->sync($request->input('artist_ids', []));
        $this->storeImages($request, $coin);

        return redirect()->route('admin.coins.index')->with('status', 'Coin created.');
    }

    public function edit(Coin $coin)
    {
        return view('admin.coins.edit', [
            'coin'            => $coin,
            'allSeries'       => Series::forSelect(),
            'allArtists'      => Artist::orderBy('name')->get(),
            'selectedArtists' => $coin->artists->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Coin $coin)
    {
        $data = $this->validated($request, $coin);

        $coin->update($data);

        $coin->artists()->sync($request->input('artist_ids', []));
        $this->storeImages($request, $coin);

        return redirect()->route('admin.coins.index')->with('status', 'Coin updated.');
    }

    public function destroy(Coin $coin)
    {
        foreach (['front_image', 'back_image'] as $field) {
            if ($coin->$field) {
                Storage::disk('public')->delete($coin->$field);
            }
        }

        $coin->delete();

        return redirect()->route('admin.coins.index')->with('status', 'Coin deleted.');
    }

    protected function validated(Request $request, ?Coin $coin = null): array
    {
        $data = $request->validate([
            'title'                  => ['required', 'array'],
            'title.bg'               => ['nullable', 'string', 'max:255'],
            'title.en'               => ['nullable', 'string', 'max:255'],
            'title.de'               => ['nullable', 'string', 'max:255'],

            'series_id'              => ['nullable', 'exists:series,id'],

            'artist_ids'             => ['nullable', 'array'],
            'artist_ids.*'           => ['exists:artists,id'],

            'year'                   => ['nullable', 'integer', 'min:0', 'max:2100'],
            'issue_date'             => ['nullable', 'date'],
            'denomination'           => ['nullable', 'string', 'max:255'],
            'metal'                  => ['nullable', 'string', 'max:255'],
            'quality'                => ['nullable', 'string', 'max:255'],
            'weight'                 => ['nullable', 'string', 'max:255'],
            'diameter'               => ['nullable', 'string', 'max:255'],
            'mintage'                => ['nullable', 'string', 'max:255'],

            'edge'                   => ['nullable', 'array'],
            'edge.bg'                => ['nullable', 'string', 'max:255'],
            'edge.en'                => ['nullable', 'string', 'max:255'],
            'edge.de'                => ['nullable', 'string', 'max:255'],

            'mint'                   => ['nullable', 'array'],
            'mint.bg'                => ['nullable', 'string', 'max:255'],
            'mint.en'                => ['nullable', 'string', 'max:255'],
            'mint.de'                => ['nullable', 'string', 'max:255'],

            'front_image'            => ['nullable', 'image', 'max:4096'],
            'front_description'      => ['nullable', 'array'],
            'front_description.bg'   => ['nullable', 'string'],
            'front_description.en'   => ['nullable', 'string'],
            'front_description.de'   => ['nullable', 'string'],

            'back_image'             => ['nullable', 'image', 'max:4096'],
            'back_description'       => ['nullable', 'array'],
            'back_description.bg'    => ['nullable', 'string'],
            'back_description.en'    => ['nullable', 'string'],
            'back_description.de'    => ['nullable', 'string'],

            'description'            => ['nullable', 'array'],
            'description.bg'         => ['nullable', 'string'],
            'description.en'         => ['nullable', 'string'],
            'description.de'         => ['nullable', 'string'],
        ]);

        if (! array_filter($data['title'] ?? [])) {
            throw ValidationException::withMessages([
                'title.en' => 'Please provide a title in at least one language.',
            ]);
        }

        // artist_ids is handled separately via sync(), not mass assignment.
        unset($data['artist_ids']);

        return $data;
    }

    protected function storeImages(Request $request, Coin $coin): void
    {
        $updates = [];

        foreach (['front_image', 'back_image'] as $field) {
            if ($request->hasFile($field)) {
                if ($coin->$field) {
                    Storage::disk('public')->delete($coin->$field);
                }
                $updates[$field] = $request->file($field)->store('coins', 'public');
            }
        }

        if ($updates) {
            $coin->update($updates);
        }
    }
}
