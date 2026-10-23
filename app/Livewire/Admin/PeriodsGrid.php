<?php

namespace App\Livewire\Admin;

use App\Models\Period;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class PeriodsGrid extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'name';

    /** @var 'asc'|'desc' */
    public string $sortDirection = 'asc';

    public string $paginationTheme = 'bootstrap';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if (! in_array($column, ['name', 'slug', 'coins_count'], true)) {
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

    public function render(): View
    {
        $periods = Period::query()
            ->withCount('coins')
            ->when($this->search !== '', fn ($query) => $query->search($this->search))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(20);

        return view('livewire.admin.periods-grid', compact('periods'));
    }
}
