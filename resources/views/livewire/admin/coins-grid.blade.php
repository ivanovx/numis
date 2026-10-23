<div>
    <div class="row g-2 mb-3">
        <div class="col-lg-4">
            <label for="coin-search" class="visually-hidden">Търси монети</label>
            <input id="coin-search" type="search" class="form-control" placeholder="Търси по заглавие, серия, метал, каталожен №..."
                   wire:model.live.debounce.300ms="search">
        </div>
        <div class="col-sm-6 col-lg-2">
            <select class="form-select" wire:model.live="seriesId" aria-label="Филтрирай по серия">
                <option value="">Всички серии</option>
                @foreach ($series as $term)
                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-lg-2">
            <select class="form-select" wire:model.live="category" aria-label="Филтрирай по категория">
                <option value="">Всички категории</option>
                @foreach ($categories as $value)
                    <option value="{{ $value }}">{{ __('catalog.categories.' . $value) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-lg-2">
            <select class="form-select" wire:model.live="metal" aria-label="Филтрирай по метал">
                <option value="">Всички метали</option>
                @foreach ($metals as $value)
                    <option value="{{ $value }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-lg-2">
            <button type="button" class="btn btn-outline-secondary w-100" wire:click="clearFilters">
                Изчисти филтрите
            </button>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-lg-3">
            <select class="form-select" wire:model.live="completeness" aria-label="Филтрирай по пълнота на данните">
                <option value="">Всички записи</option>
                <option value="incomplete">Само непълни записи</option>
                <option value="complete">Само пълни записи ({{ count($completenessFields) }}/{{ count($completenessFields) }})</option>
            </select>
        </div>
        <div class="col-sm-6 col-lg-3">
            <select class="form-select" wire:model.live="missing" aria-label="Филтрирай по липсващо поле">
                <option value="">Липсващо поле: всяко</option>
                @foreach ($completenessFields as $field => $label)
                    <option value="{{ $field }}">Липсва: {{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <span class="small text-muted">Избрани: {{ count($selected) }}</span>
        <select class="form-select form-select-sm" style="max-width: 220px" wire:model.live="bulkSeriesId">
            <option value="">Присвои избраните към серия...</option>
            @foreach ($series as $term)
                <option value="{{ $term->id }}">{{ $term->name }}</option>
            @endforeach
        </select>
        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="assignSelectedSeries"
                wire:loading.attr="disabled" @disabled(count($selected) === 0)>
            Присвои серия
        </button>
        <select class="form-select form-select-sm" style="max-width: 260px" wire:model.live="bulkPeriodId"
                aria-label="Присвои избраните монети към исторически период">
            <option value="">Присвои избраните към период...</option>
            <option value="none">Премахни периода от избраните</option>
            @foreach ($periods as $period)
                <option value="{{ $period->id }}">{{ $period->name }}</option>
            @endforeach
        </select>
        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="assignSelectedPeriod"
                wire:loading.attr="disabled" @disabled(count($selected) === 0 || $bulkPeriodId === '')>
            Присвои период
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="deleteSelected"
                wire:confirm="Да се изтрият ли всички избрани монети? Това действие е необратимо."
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
                               @checked($coins->count() > 0 && $coins->pluck('id')->every(fn ($id) => in_array($id, $selected)))
                               wire:click="togglePageSelection(@js($coins->pluck('id')->values()))">
                    </th>
                    <th>Заглавие</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-decoration-none text-dark fw-bold"
                                wire:click="sort('year')">
                            Година @if ($sortBy === 'year') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-decoration-none text-dark fw-bold"
                                wire:click="sort('denomination')">
                            Номинал @if ($sortBy === 'denomination') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                        </button>
                    </th>
                    <th>Метал</th>
                    <th>Серия</th>
                    <th>Исторически период</th>
                    <th>Художници</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-decoration-none text-dark fw-bold"
                                wire:click="sort('completeness')" title="Сортирай по пълнота на данните">
                            Данни @if ($sortBy === 'completeness') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                        </button>
                    </th>
                    <th>Състояние</th>
                    <th class="text-end">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($coins as $coin)
                    <tr wire:key="coin-{{ $coin->id }}">
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input" value="{{ $coin->id }}"
                                   wire:model.live="selected" aria-label="Избери {{ $coin->title }}">
                        </td>
                        <td>{{ $coin->title ?: '—' }}</td>
                        <td>{{ $coin->year ?: '—' }}</td>
                        <td>{{ $coin->denomination ?: '—' }}</td>
                        <td>{{ $coin->metal ?: '—' }}</td>
                        <td>{{ $coin->series?->name ?: '—' }}</td>
                        <td>{{ $coin->period?->name ?: '—' }}</td>
                        <td>{{ $coin->artistNames() ?: '—' }}</td>
                        <td>@include('admin.coins._completeness', ['coin' => $coin])</td>
                        <td class="text-nowrap">
                            @foreach ($this->statusFlags($coin) as $status)
                                <span class="badge {{ $status === 'Пълна' ? 'text-bg-success' : 'text-bg-warning' }} mb-1">
                                    {{ $status }}
                                </span>
                            @endforeach
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.coins.edit', $coin) }}" class="btn btn-sm btn-outline-secondary">Редактирай</a>
                            <form action="{{ route('admin.coins.destroy', $coin) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Да се изтрие ли тази монета?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Изтрий</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="text-center py-4">Няма намерени монети.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $coins->links() }}
    </div>
</div>