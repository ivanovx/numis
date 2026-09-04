<?php

use App\Models\Artist;
use App\Models\Coin;
use App\Models\Series;
use App\Models\User;
use Livewire\Livewire;

test('authenticated users can visit the series grid', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('admin.series.index'));

    $response->assertOk();
    $response->assertSee('Search name or slug...');
    $response->assertSee(route('admin.series.create'));
});

test('authenticated users can visit the artists grid', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('admin.artists.index'));

    $response->assertOk();
    $response->assertSee('Search name or slug...');
    $response->assertSee(route('admin.artists.create'));
});

test('series and artists grids render existing records with CRUD links', function () {
    $this->actingAs(User::factory()->create());

    $series = Series::create(['name' => ['bg' => 'Български серии'], 'slug' => 'bulgarian-series']);
    $artist = Artist::create(['name' => 'Ivan Vazov', 'slug' => 'ivan-vazov']);

    $this->get(route('admin.series.index'))
        ->assertSee('bulgarian-series')
        ->assertSee(route('admin.series.edit', $series));

    $this->get(route('admin.artists.index'))
        ->assertSee('Ivan Vazov')
        ->assertSee(route('admin.artists.edit', $artist));
});

test('coins can be assigned to a series in bulk', function () {
    $this->actingAs(User::factory()->create());

    $series = Series::create(['name' => ['bg' => 'New series'], 'slug' => 'new-series']);
    $coins = collect([
        Coin::create(['title' => ['bg' => 'Coin one'], 'category' => Coin::CATEGORIES[0]]),
        Coin::create(['title' => ['bg' => 'Coin two'], 'category' => Coin::CATEGORIES[0]]),
    ]);

    Livewire::test('admin.coins-grid')
        ->set('selected', $coins->pluck('id')->all())
        ->set('bulkSeriesId', (string) $series->id)
        ->call('assignSelectedSeries');

    expect(Coin::whereIn('id', $coins->pluck('id'))->pluck('series_id')->all())
        ->toBe([$series->id, $series->id]);
});

test('series and artists can be deleted in bulk', function () {
    $this->actingAs(User::factory()->create());

    $series = Series::create(['name' => ['bg' => 'Delete series'], 'slug' => 'delete-series']);
    $artist = Artist::create(['name' => 'Delete artist', 'slug' => 'delete-artist']);

    Livewire::test('admin.series-grid')
        ->set('selected', [$series->id])
        ->call('deleteSelected');

    Livewire::test('admin.artists-grid')
        ->set('selected', [$artist->id])
        ->call('deleteSelected');

    expect(Series::find($series->id))->toBeNull()
        ->and(Artist::find($artist->id))->toBeNull();
});
