<?php

use App\Models\Coin;
use App\Models\Period;
use App\Models\User;
use Livewire\Livewire;

test('authenticated users can visit the coins grid', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('admin.coins.index'));

    $response->assertOk();
    $response->assertSee('Търси по заглавие, серия, метал, каталожен №...');
    $response->assertSee(route('admin.coins.create'));
    $response->assertSee('lang="bg"', false);
    $response->assertSee('Периоди');
});

test('coins grid shows missing data statuses', function () {
    $this->actingAs(User::factory()->create());
    Coin::create([
        'title' => ['bg' => 'Непълна монета'],
        'category' => Coin::CATEGORIES[0],
    ]);

    $response = $this->get(route('admin.coins.index'));

    $response->assertOk()
        ->assertSee('Липсват изображения')
        ->assertSee('Липсват преводи')
        ->assertSee('Липсва серия')
        ->assertSee('Липсва година')
        ->assertSee('Липсва описание');
});

test('coins are ordered by denomination and then by year by default', function () {
    $this->actingAs(User::factory()->create());

    foreach ([
        ['One lev, later year', '1 лев', 1901],
        ['One cent', '1 cent', 2000],
        ['One stotinka, later year', '1 стотинка', 1901],
        ['Five euro', '5 euro', 2001],
        ['One lev, earlier year', '1 лев', 1900],
        ['One stotinka, earlier year', '1 стотинка', 1900],
    ] as [$title, $denomination, $year]) {
        Coin::create([
            'title' => ['bg' => $title],
            'category' => Coin::CATEGORIES[0],
            'denomination' => $denomination,
            'year' => $year,
        ]);
    }

    Livewire::test('admin.coins-grid')
        ->assertSeeInOrder([
            'One stotinka, earlier year',
            'One stotinka, later year',
            'One lev, earlier year',
            'One lev, later year',
            'One cent',
            'Five euro',
        ]);
});

test('selected coins can be assigned to and removed from a historical period', function () {
    $this->actingAs(User::factory()->create());

    $period = Period::create(['name' => 'Народна република', 'slug' => 'narodna-republika']);
    $coins = collect([
        Coin::create(['title' => ['bg' => 'Coin one'], 'category' => Coin::CATEGORIES[0]]),
        Coin::create(['title' => ['bg' => 'Coin two'], 'category' => Coin::CATEGORIES[0]]),
    ]);

    Livewire::test('admin.coins-grid')
        ->set('selected', $coins->pluck('id')->all())
        ->set('bulkPeriodId', (string) $period->id)
        ->call('assignSelectedPeriod');

    expect(Coin::whereIn('id', $coins->pluck('id'))->pluck('period_id')->all())
        ->toBe([$period->id, $period->id]);

    Livewire::test('admin.coins-grid')
        ->set('selected', $coins->pluck('id')->all())
        ->set('bulkPeriodId', 'none')
        ->call('assignSelectedPeriod');

    expect(Coin::whereIn('id', $coins->pluck('id'))->whereNotNull('period_id')->exists())
        ->toBeFalse();
});
