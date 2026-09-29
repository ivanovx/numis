<?php

namespace App\Http\Controllers;

use App\Models\Artist;

class ArtistsController extends Controller {
    public function index() {
        $artists = Artist::query()
            ->withCount('coins')
            ->orderBy('name')
            ->get();

        return view('artists.index', [
            'artists' => $artists
        ]);
    }

    public function show(string $locale, Artist $artist) {
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
