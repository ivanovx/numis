@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Series</h1>
        <a href="{{ route('admin.series.create') }}" class="btn btn-primary">➕ Add Series</a>
    </div>

    <table class="table table-striped bg-white">
        <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Parent</th>
                <th>Coins</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($series as $term)
                <tr>
                    <td>{{ $term->name }}</td>
                    <td>{{ $term->slug }}</td>
                    <td>{{ $term->parent->name ?? '—' }}</td>
                    <td>{{ $term->coins()->count() }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.series.edit', $term) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form action="{{ route('admin.series.destroy', $term) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this series?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No series found.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $series->links() }}
@endsection
