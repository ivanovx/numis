<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CoinController extends Controller
{
    public function index()
    {
        $coins = Coin::with('series')->orderBy('title')->paginate(20)->withQueryString();

        return view('admin.coins.index', compact('coins'));
    }

    public function create()
    {
        return view('admin.coins.create', [
            'coin'      => new Coin(),
            'allSeries' => Series::flatTree(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $coin = Coin::create($data);

        $this->storeImages($request, $coin);
        $coin->series()->sync($request->input('series', []));

        return redirect()->route('admin.coins.index')->with('status', 'Coin created.');
    }

    public function edit(Coin $coin)
    {
        return view('admin.coins.edit', [
            'coin'      => $coin,
            'allSeries' => Series::flatTree(),
            'selected'  => $coin->series->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Coin $coin)
    {
        $data = $this->validated($request, $coin);

        $coin->update($data);

        $this->storeImages($request, $coin);
        $coin->series()->sync($request->input('series', []));

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
        return $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'year'         => ['nullable', 'integer', 'min:0', 'max:2100'],
            'denomination' => ['nullable', 'string', 'max:255'],
            'metal'        => ['nullable', 'string', 'max:255'],
            'diameter'     => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'front_image'  => ['nullable', 'image', 'max:4096'],
            'back_image'   => ['nullable', 'image', 'max:4096'],
            'series'       => ['nullable', 'array'],
            'series.*'     => ['exists:series,id'],
        ]);
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
