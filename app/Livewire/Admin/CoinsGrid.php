<?php

namespace App\Livewire\Admin;

use App\Models\Coin;
use App\Models\Series;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CoinsGrid extends Component
{
    use WithPagination;

    public string $search = '';

    public string $seriesId = '';

    public string $category = '';

    public string $metal = '';

    /** '' | 'complete' | 'incomplete' — linkable, e.g. from the dashboard. */
    #[Url]
    public string $completeness = '';

    /** '' | one of Coin::COMPLETENESS_FIELDS — show only coins missing that field. */
    #[Url]
    public string $missing = '';

    public string $sortBy = 'year';

    /** @var 'asc'|'desc' */
    public string $sortDirection = 'desc';

    public int $perPage = 20;

    public string $paginationTheme = 'bootstrap';

    /** @var array<int, int|string> */
    public array $selected = [];

    public string $bulkSeriesId = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'seriesId', 'category', 'metal', 'completeness', 'missing', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function sort(string $column): void
    {
        if (! in_array($column, ['year', 'denomination', 'metal', 'completeness'], true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'seriesId', 'category', 'metal', 'completeness', 'missing']);
        $this->selected = [];
        $this->resetPage();
    }

    /** @param  array<int, int|string>  $ids */
    public function togglePageSelection(array $ids): void
    {
        $ids = array_map('intval', $ids);

        if (count(array_intersect($ids, $this->selected)) === count($ids)) {
            $this->selected = array_values(array_diff($this->selected, $ids));
        } else {
            $this->selected = array_values(array_unique(array_merge($this->selected, $ids)));
        }
    }

    public function deleteSelected(): void
    {
        Coin::whereKey($this->selected)->get()->each(function (Coin $coin): void {
            foreach (['front_image', 'back_image'] as $field) {
                if ($coin->$field) {
                    Storage::disk('public')->delete($coin->$field);
                }
            }

            $coin->delete();
        });

        $this->selected = [];
        $this->resetPage();
        session()->flash('status', 'Selected coins deleted.');
    }

    public function assignSelectedSeries(): void
    {
        if ($this->bulkSeriesId === '') {
            return;
        }

        Coin::whereKey($this->selected)->update(['series_id' => $this->bulkSeriesId]);

        $this->selected = [];
        $this->bulkSeriesId = '';
        session()->flash('status', 'Selected coins assigned to the series.');
    }

    /** @return list<string> */
    public function statusFlags(Coin $coin): array
    {
        $flags = [];

        if (! $coin->front_image || ! $coin->back_image) {
            $flags[] = 'Missing images';
        }

        if (collect(['bg', 'en', 'de'])->contains(fn ($locale) => ! filled($coin->translation('title', $locale)))) {
            $flags[] = 'Missing translations';
        }

        if (! $coin->series_id) {
            $flags[] = 'Missing series';
        }

        if (! $coin->year) {
            $flags[] = 'Missing year';
        }

        if (! collect(['bg', 'en', 'de'])->contains(fn ($locale) => filled($coin->translation('description', $locale)))) {
            $flags[] = 'Missing description';
        }

        return $flags ?: ['Complete'];
    }

    public function render(): View
    {
        $coins = Coin::query()
            ->with(['series', 'artists'])
            ->when($this->search !== '', function ($query) {
                $search = '%'.$this->search.'%';

                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', $search)
                        ->orWhere('denomination', 'like', $search)
                        ->orWhere('metal', 'like', $search)
                        ->orWhere('km_number', 'like', $search)
                        ->orWhere('numista_number', 'like', $search)
                        ->orWhere('bnb_number', 'like', $search)
                        ->orWhere('other_references', 'like', $search)
                        ->orWhereHas('series', fn ($query) => $query->where('slug', 'like', $search));
                });
            })
            ->when($this->seriesId !== '', fn ($query) => $query->where('series_id', $this->seriesId))
            ->when($this->category !== '', fn ($query) => $query->where('category', $this->category))
            ->when($this->metal !== '', fn ($query) => $query->where('metal', $this->metal))
            ->when($this->completeness !== '', fn ($query) => $query->completenessState($this->completeness))
            ->when($this->missing !== '', fn ($query) => $query->missingField($this->missing))
            ->when(
                $this->sortBy === 'completeness',
                fn ($query) => $query->orderByCompleteness($this->sortDirection)->orderByDesc('year'),
                fn ($query) => $query->orderBy($this->sortBy, $this->sortDirection),
            )
            ->paginate($this->perPage);

        return view('livewire.admin.coins-grid', [
            'coins' => $coins,
            'series' => Series::forSelect(),
            'categories' => Coin::CATEGORIES,
            'metals' => Coin::METALS,
            'completenessFields' => Coin::COMPLETENESS_FIELDS,
        ]);
    }
}
