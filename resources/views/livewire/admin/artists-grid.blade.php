<div>
    <div class="d-flex gap-2 mb-3">
        <label for="artist-search" class="visually-hidden">Търси художници</label>
        <input id="artist-search" type="search" class="form-control" placeholder="Търси по име или кратко име..."
               wire:model.live.debounce.300ms="search">
        <button type="button" class="btn btn-outline-secondary text-nowrap" wire:click="clearSearch">
            Изчисти
        </button>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <span class="small text-muted">Избрани: {{ count($selected) }}</span>
        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="deleteSelected"
                wire:confirm="Да се изтрият ли всички избрани художници? Връзките им с монетите ще бъдат премахнати."
                wire:loading.attr="disabled" @disabled(count($selected) === 0)>
            Изтрий избраните
        </button>
        <button type="button" class="btn btn-sm btn-link" wire:click="$set('selected', [])"
                @disabled(count($selected) === 0)>
            Изчисти избора
        </button>
    </div>

    <div class="table-responsive bg-white border rounded">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 2.5rem">
                        <input type="checkbox" class="form-check-input" aria-label="Избери текущата страница"
                               @checked($artists->count() > 0 && $artists->pluck('id')->every(fn ($id) => in_array($id, $selected)))
                               wire:click="togglePageSelection(@js($artists->pluck('id')->values()))">
                    </th>
                    <th><button type="button" class="btn btn-link p-0 text-decoration-none text-dark fw-bold" wire:click="sort('name')">Име @if ($sortBy === 'name') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif</button></th>
                    <th><button type="button" class="btn btn-link p-0 text-decoration-none text-dark fw-bold" wire:click="sort('slug')">Кратко име @if ($sortBy === 'slug') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif</button></th>
                    <th><button type="button" class="btn btn-link p-0 text-decoration-none text-dark fw-bold" wire:click="sort('coins_count')">Монети @if ($sortBy === 'coins_count') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif</button></th>
                    <th class="text-end">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($artists as $artist)
                    <tr wire:key="artist-{{ $artist->id }}">
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input" value="{{ $artist->id }}"
                                   wire:model.live="selected" aria-label="Избери {{ $artist->name }}">
                        </td>
                        <td>{{ $artist->name }}</td>
                        <td>{{ $artist->slug }}</td>
                        <td>{{ $artist->coins_count }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.artists.edit', $artist) }}" class="btn btn-sm btn-outline-secondary">Редактирай</a>
                            <form action="{{ route('admin.artists.destroy', $artist) }}" method="POST" class="d-inline" onsubmit="return confirm('Да се изтрие ли този художник? Монетите ще останат, но без тази връзка.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Изтрий</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4">Няма намерени художници.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $artists->links() }}</div>
</div>