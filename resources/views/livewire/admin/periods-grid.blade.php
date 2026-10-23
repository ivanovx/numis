<div>
    <div class="d-flex gap-2 mb-3">
        <label for="period-search" class="visually-hidden">Търси исторически периоди</label>
        <input id="period-search" type="search" class="form-control" placeholder="Търси по име или кратко име..."
               wire:model.live.debounce.300ms="search">
    </div>

    <div class="table-responsive bg-white border rounded">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Име</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-decoration-none text-dark fw-bold"
                                wire:click="sort('slug')">
                            Кратко име @if ($sortBy === 'slug') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-decoration-none text-dark fw-bold"
                                wire:click="sort('coins_count')">
                            Монети @if ($sortBy === 'coins_count') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                        </button>
                    </th>
                    <th class="text-end">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($periods as $period)
                    <tr wire:key="period-{{ $period->id }}">
                        <td>{{ $period->name }}</td>
                        <td>{{ $period->slug }}</td>
                        <td>{{ $period->coins_count }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.periods.edit', $period) }}" class="btn btn-sm btn-outline-secondary">Редактирай</a>
                            <form action="{{ route('admin.periods.destroy', $period) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Да се изтрие ли този период? Монетите ще останат без период.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Изтрий</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-4">Няма намерени исторически периоди.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $periods->links() }}</div>
</div>
