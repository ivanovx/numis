<?php

use App\Models\Artist;
use App\Models\Coin;
use App\Models\Series;

test('public statistics page shows catalog totals and data quality', function () {
    Series::create(['name' => ['bg' => 'Серия'], 'slug' => 'series']);
    Artist::create(['name' => 'Автор', 'slug' => 'author']);
    Coin::create([
        'title' => ['bg' => 'Пълна монета', 'en' => 'Complete coin', 'de' => 'Vollständige Münze'],
        'category' => 'exchange',
        'year' => 2026,
        'front_image' => 'coins/front.jpg',
        'back_image' => 'coins/back.jpg',
    ]);
    Coin::create(['title' => ['bg' => 'Непълна монета'], 'category' => 'commemorative', 'year' => 2025]);

    $response = $this->get(route('catalog.statistics', ['locale' => 'bg']));

    $response->assertOk()
        ->assertSee('Общо монети')
        ->assertSee('2')
        ->assertSee('2026')
        ->assertSee('2025')
        ->assertSee('Липсващи снимки')
        ->assertSee('Липсващи преводи')
        ->assertSee('Художници и брой монети')
        ->assertSee('Автор');
});

test('public statistics page shows each artist coin count', function () {
    $artist = Artist::create(['name' => 'Автор с две монети', 'slug' => 'author-two-coins']);

    $firstCoin = Coin::create(['title' => ['bg' => 'Първа'], 'category' => 'exchange']);
    $secondCoin = Coin::create(['title' => ['bg' => 'Втора'], 'category' => 'exchange']);
    $artist->coins()->attach([$firstCoin->id, $secondCoin->id]);

    $this->get(route('catalog.statistics', ['locale' => 'bg']))
        ->assertOk()
        ->assertSee('Автор с две монети')
        ->assertSeeInOrder(['Автор с две монети', '2']);
});
