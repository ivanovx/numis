<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    public function store(Request $request)
    {
        Artist::create($this->validated($request));

        return redirect()->route('admin.artists.index')->with('status', 'Artist created.');
    }

    public function edit(Artist $artist)
    {
        return view('admin.artists.edit', compact('artist'));
    }

    public function update(Request $request, Artist $artist)
    {
        $artist->update($this->validated($request, $artist));

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
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:artists,slug,'.$id],
        ]);

        $data['slug'] = $data['slug'] ? Str::slug($data['slug']) : Str::slug($data['name']);

        return $data;
    }
}
