<div>
    <div class="row g-2 mb-3">
        <div class="col-lg-4">
            <label for="coin-search" class="visually-hidden">Search coins</label>
            <input id="coin-search" type="search" class="form-control" placeholder="Search title, series, metal..."
                   wire:model.live.debounce.300ms="search">
        </div>
        <div class="col-sm-6 col-lg-2">
            <select class="form-select" wire:model.live="seriesId" aria-label="Filter by series">
                <option value="">All series</option>
                @foreach ($series as $term)
                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-lg-2">
            <select class="form-select" wire:model.live="category" aria-label="Filter by category">
                <option value="">All categories</option>
                @foreach ($categories as $value)
                    <option value="{{ $value }}">{{ ucfirst($value) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-lg-2">
            <select class="form-select" wire:model.live="metal" aria-label="Filter by metal">
                <option value="">All metals</option>
                @foreach ($metals as $value)
                    <option value="{{ $value }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-lg-2">
            <button type="button" class="btn btn-outline-secondary w-100" wire:click="clearFilters">
                Clear filters
            </button>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <span class="small text-muted">{{ count($selected) }} selected</span>
        <select class="form-select form-select-sm" style="max-width: 220px" wire:model.live="bulkSeriesId">
            <option value="">Assign selected to series...</option>
            @foreach ($series as $term)
                <option value="{{ $term->id }}">{{ $term->name }}</option>
            @endforeach
        </select>
        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="assignSelectedSeries"
                wire:loading.attr="disabled" @disabled(count($selected) === 0)>
            Assign series
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="deleteSelected"
                wire:confirm="Delete all selected coins? This cannot be undone."
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
                               @checked($coins->count() > 0 && $coins->pluck('id')->every(fn ($id) => in_array($id, $selected)))
                               wire:click="togglePageSelection(@js($coins->pluck('id')->values()))">
                    </th>
                    <th>Title</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-decoration-none text-dark fw-bold"
                                wire:click="sort('year')">
                            Year @if ($sortBy === 'year') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                        </button>
                    </th>
                    <th>Denomination</th>
                    <th>Metal</th>
                    <th>Series</th>
                    <th>Artists</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($coins as $coin)
                    <tr wire:key="coin-{{ $coin->id }}">
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input" value="{{ $coin->id }}"
                                   wire:model.live="selected" aria-label="Select {{ $coin->title }}">
                        </td>
                        <td>{{ $coin->title ?: '—' }}</td>
                        <td>{{ $coin->year ?: '—' }}</td>
                        <td>{{ $coin->denomination ?: '—' }}</td>
                        <td>{{ $coin->metal ?: '—' }}</td>
                        <td>{{ $coin->series?->name ?: '—' }}</td>
                        <td>{{ $coin->artistNames() ?: '—' }}</td>
                        <td class="text-nowrap">
                            @foreach ($this->statusFlags($coin) as $status)
                                <span class="badge {{ $status === 'Complete' ? 'text-bg-success' : 'text-bg-warning' }} mb-1">
                                    {{ $status }}
                                </span>
                            @endforeach
                        </td>
                        <td class="text-end text-nowrap">
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
                    <tr><td colspan="9" class="text-center py-4">No coins found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $coins->links() }}
    </div>
</div>