<?php

namespace App\Http\Controllers;

use App\Models\Artist;

class ArtistsController extends Controller
{
    public function index()
    {
        return view('artists.index', [
            'artists' => Artist::query()
                ->withCount('coins')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function show(string $locale, Artist $artist)
    {
        $artist->load([
            'coins' => fn ($query) => $query
                ->with(['series', 'artists'])
                ->orderByDesc('year')
                ->orderBy('id'),
        ]);

        return view('artists.show', [
            'artist' => $artist,
        ]);
    }
}
