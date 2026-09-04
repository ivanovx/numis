<div>
    <div class="d-flex gap-2 mb-3">
        <label for="artist-search" class="visually-hidden">Search artists</label>
        <input id="artist-search" type="search" class="form-control" placeholder="Search name or slug..."
               wire:model.live.debounce.300ms="search">
        <button type="button" class="btn btn-outline-secondary text-nowrap" wire:click="clearSearch">
            Clear
        </button>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <span class="small text-muted">{{ count($selected) }} selected</span>
        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="deleteSelected"
                wire:confirm="Delete all selected artists? Their coin credits will be removed."
                wire:loading.attr="disabled" @disabled(count($selected) === 0)>
            Delete selected
        </button>
        <button type="button" class="btn btn-sm btn-link" wire:click="$set('selected', [])"
                @disabled(count($selected) === 0)>
            Clear selection
        </button>
    </div>

    <div class="table-responsive bg-white border rounded">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 2.5rem">
                        <input type="checkbox" class="form-check-input" aria-label="Select current page"
                               @checked($artists->count() > 0 && $artists->pluck('id')->every(fn ($id) => in_array($id, $selected)))
                               wire:click="togglePageSelection(@js($artists->pluck('id')->values()))">
                    </th>
                    <th><button type="button" class="btn btn-link p-0 text-decoration-none text-dark fw-bold" wire:click="sort('name')">Name @if ($sortBy === 'name') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif</button></th>
                    <th><button type="button" class="btn btn-link p-0 text-decoration-none text-dark fw-bold" wire:click="sort('slug')">Slug @if ($sortBy === 'slug') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif</button></th>
                    <th><button type="button" class="btn btn-link p-0 text-decoration-none text-dark fw-bold" wire:click="sort('coins_count')">Coins @if ($sortBy === 'coins_count') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif</button></th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($artists as $artist)
                    <tr wire:key="artist-{{ $artist->id }}">
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input" value="{{ $artist->id }}"
                                   wire:model.live="selected" aria-label="Select {{ $artist->name }}">
                        </td>
                        <td>{{ $artist->name }}</td>
                        <td>{{ $artist->slug }}</td>
                        <td>{{ $artist->coins_count }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.artists.edit', $artist) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form action="{{ route('admin.artists.destroy', $artist) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this artist? Coins keep existing, they just lose this credit.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4">No artists found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $artists->links() }}</div>
</div>