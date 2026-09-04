<div>
    <div class="d-flex gap-2 mb-3">
        <label for="series-search" class="visually-hidden">Search series</label>
        <input id="series-search" type="search" class="form-control" placeholder="Search name or slug..."
               wire:model.live.debounce.300ms="search">
        <button type="button" class="btn btn-outline-secondary text-nowrap" wire:click="clearSearch">
            Clear
        </button>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <span class="small text-muted">{{ count($selected) }} selected</span>
        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="deleteSelected"
                wire:confirm="Delete all selected series? Their coins will become unassigned."
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
                               @checked($series->count() > 0 && $series->pluck('id')->every(fn ($id) => in_array($id, $selected)))
                               wire:click="togglePageSelection(@js($series->pluck('id')->values()))">
                    </th>
                    <th>Name</th>
                    <th><button type="button" class="btn btn-link p-0 text-decoration-none text-dark fw-bold" wire:click="sort('slug')">Slug @if ($sortBy === 'slug') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif</button></th>
                    <th><button type="button" class="btn btn-link p-0 text-decoration-none text-dark fw-bold" wire:click="sort('coins_count')">Coins @if ($sortBy === 'coins_count') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif</button></th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($series as $term)
                    <tr wire:key="series-{{ $term->id }}">
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input" value="{{ $term->id }}"
                                   wire:model.live="selected" aria-label="Select {{ $term->name }}">
                        </td>
                        <td>{{ $term->name ?: '—' }}</td>
                        <td>{{ $term->slug }}</td>
                        <td>{{ $term->coins_count }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.series.edit', $term) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form action="{{ route('admin.series.destroy', $term) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this series? Coins in it will become unassigned, not deleted.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4">No series found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $series->links() }}</div>
</div>