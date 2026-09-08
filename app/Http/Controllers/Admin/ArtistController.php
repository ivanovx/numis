<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Services\CoinTranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ArtistController extends Controller
{
    public function index()
    {
        return view('admin.artists.index');
    }

    public function create()
    {
        return view('admin.artists.create', ['artist' => new Artist]);
    }

    public function store(Request $request, CoinTranslationService $translator)
    {
        $data = $this->validated($request);

        try {
            $data = $translator->translateFields($data, ['name']);
        } catch (Throwable $exception) {
            throw ValidationException::withMessages([
                'name.bg' => 'Automatic translation failed: '.$exception->getMessage(),
            ]);
        }

        Artist::create($data);

        return redirect()->route('admin.artists.index')->with('status', 'Artist created.');
    }

    public function edit(Artist $artist)
    {
        return view('admin.artists.edit', compact('artist'));
    }

    public function update(Request $request, Artist $artist)
    {
        $data = $this->validated($request, $artist);

        $artist->update($data);

        return redirect()->route('admin.artists.index')->with('status', 'Artist updated.');
    }

    public function destroy(Artist $artist)
    {
        $artist->delete(); // coins keep existing, just lose this artist credit

        return redirect()->route('admin.artists.index')->with('status', 'Artist deleted.');
    }

    protected function validated(Request $request, ?Artist $artist = null): array
    {
        $id = $artist?->id;

        $data = $request->validate([
            'name' => ['required', 'array'],
            'name.bg' => ['nullable', 'string', 'max:255'],
            'name.en' => ['nullable', 'string', 'max:255'],
            'name.de' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:artists,slug,'.$id],
        ]);

        $firstName = collect($data['name'])->first(fn ($value) => filled($value));

        if (! $firstName) {
            throw ValidationException::withMessages([
                'name.en' => 'Please provide a name in at least one language.',
            ]);
        }

        $data['slug'] = $data['slug'] ? Str::slug($data['slug']) : Str::slug($firstName);

        return $data;
    }
}
