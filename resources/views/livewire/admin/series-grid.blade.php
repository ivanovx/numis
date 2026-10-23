<div>
    <div class="d-flex gap-2 mb-3">
        <label for="series-search" class="visually-hidden">Търси серии</label>
        <input id="series-search" type="search" class="form-control" placeholder="Търси по име или кратко име..."
               wire:model.live.debounce.300ms="search">
        <button type="button" class="btn btn-outline-secondary text-nowrap" wire:click="clearSearch">
            Изчисти
        </button>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <span class="small text-muted">Избрани: {{ count($selected) }}</span>
        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="deleteSelected"
                wire:confirm="Да се изтрият ли всички избрани серии? Монетите им ще останат без серия."
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
                               @checked($series->count() > 0 && $series->pluck('id')->every(fn ($id) => in_array($id, $selected)))
                               wire:click="togglePageSelection(@js($series->pluck('id')->values()))">
                    </th>
                    <th>Име</th>
                    <th><button type="button" class="btn btn-link p-0 text-decoration-none text-dark fw-bold" wire:click="sort('slug')">Кратко име @if ($sortBy === 'slug') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif</button></th>
                    <th><button type="button" class="btn btn-link p-0 text-decoration-none text-dark fw-bold" wire:click="sort('coins_count')">Монети @if ($sortBy === 'coins_count') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif</button></th>
                    <th class="text-end">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($series as $term)
                    <tr wire:key="series-{{ $term->id }}">
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input" value="{{ $term->id }}"
                                   wire:model.live="selected" aria-label="Избери {{ $term->name }}">
                        </td>
                        <td>{{ $term->name ?: '—' }}</td>
                        <td>{{ $term->slug }}</td>
                        <td>{{ $term->coins_count }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.series.edit', $term) }}" class="btn btn-sm btn-outline-secondary">Редактирай</a>
                            <form action="{{ route('admin.series.destroy', $term) }}" method="POST" class="d-inline" onsubmit="return confirm('Да се изтрие ли тази серия? Монетите ще останат, но без серия.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Изтрий</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4">Няма намерени серии.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $series->links() }}</div>
</div>