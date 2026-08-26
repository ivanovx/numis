@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Artists</h1>
        <a href="{{ route('admin.artists.create') }}" class="btn btn-primary">➕ Add Artist</a>
    </div>

    <table class="table table-striped bg-white">
        <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Coins</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($artists as $artist)
                <tr>
                    <td>{{ $artist->name }}</td>
                    <td>{{ $artist->slug }}</td>
                    <td>{{ $artist->coins_count }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.artists.edit', $artist) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form action="{{ route('admin.artists.destroy', $artist) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this artist? Coins keep existing, they just lose this credit.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No artists found.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $artists->links() }}
@endsection
