<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Period;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class PeriodController extends Controller
{
    public function index(): View
    {
        return view('admin.periods.index');
    }

    public function create(): View
    {
        return view('admin.periods.create', ['period' => new Period]);
    }

    public function store(Request $request): RedirectResponse
    {
        Period::create($this->validated($request));

        return redirect()->route('admin.periods.index')->with('status', 'Историческият период е добавен.');
    }

    public function edit(Period $period): View
    {
        return view('admin.periods.edit', compact('period'));
    }

    public function update(Request $request, Period $period): RedirectResponse
    {
        $period->update($this->validated($request, $period));

        return redirect()->route('admin.periods.index')->with('status', 'Историческият период е редактиран.');
    }

    public function destroy(Period $period): RedirectResponse
    {
        $period->delete();

        return redirect()->route('admin.periods.index')->with('status', 'Историческият период е изтрит.');
    }

    /** @return array{name: string, slug: string} */
    protected function validated(Request $request, ?Period $period = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('periods', 'slug')->ignore($period)],
        ]);

        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        Validator::make(
            ['slug' => $data['slug']],
            ['slug' => ['required', 'max:255', Rule::unique('periods', 'slug')->ignore($period)]],
        )->validate();

        return $data;
    }
}
