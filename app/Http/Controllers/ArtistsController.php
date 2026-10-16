<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use Illuminate\Contracts\View\View;

class ArtistsController extends Controller
{
    public function index(): View
    {
        $artists = Artist::query()
            ->withCount('coins')
            ->orderBy('name')
            ->get();

        return view('artists.index', [
            'artists' => $artists,
        ]);
    }

    public function show(string $locale, Artist $artist): View
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
