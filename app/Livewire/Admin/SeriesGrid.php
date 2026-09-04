<?php

namespace App\Livewire\Admin;

use App\Models\Series;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class SeriesGrid extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'slug';

    public string $sortDirection = 'asc';

    public string $paginationTheme = 'bootstrap';

    public array $selected = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if (! in_array($column, ['slug', 'coins_count'], true)) {
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

    public function clearSearch(): void
    {
        $this->reset('search');
        $this->selected = [];
        $this->resetPage();
    }

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
        Series::whereKey($this->selected)->get()->each->delete();

        $this->selected = [];
        $this->resetPage();
        session()->flash('status', 'Selected series deleted.');
    }

    public function render(): View
    {
        $series = Series::query()
            ->withCount('coins')
            ->when($this->search !== '', function ($query) {
                $search = '%'.$this->search.'%';

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', $search)
                        ->orWhere('slug', 'like', $search);
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(20);

        return view('livewire.admin.series-grid', compact('series'));
    }
}
