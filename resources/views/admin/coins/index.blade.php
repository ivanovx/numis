@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Coins</h1>
        <a href="{{ route('admin.coins.create') }}" class="btn btn-primary">➕ Add Coin</a>
    </div>

    <table class="table table-striped bg-white">
        <thead>
            <tr>
                <th>Title</th>
                <th>Year</th>
                <th>Metal</th>
                <th>Series</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($coins as $coin)
                <tr>
                    <td>{{ $coin->title }}</td>
                    <td>{{ $coin->year }}</td>
                    <td>{{ $coin->metal }}</td>
                    <td>{{ $coin->seriesNames() }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.coins.edit', $coin) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form action="{{ route('admin.coins.destroy', $coin) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this coin?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No coins found.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $coins->links() }}
@endsection
