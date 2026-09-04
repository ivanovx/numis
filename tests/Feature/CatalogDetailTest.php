<?php

use App\Models\Coin;
use App\Models\Series;

test('a coin has a localized public detail page', function () {
    $series = Series::create([
        'name' => ['bg' => 'Български серии', 'en' => 'Bulgarian series'],
        'slug' => 'bulgarian-series',
    ]);

    $coin = Coin::create([
        'title' => ['bg' => 'Златна монета', 'en' => 'Gold coin'],
        'series_id' => $series->id,
        'category' => Coin::CATEGORIES[1],
        'year' => 2026,
        'description' => ['bg' => '<p>Описание</p>', 'en' => '<p>Description</p>'],
    ]);

    $this->get(route('catalog.coin', ['locale' => 'en', 'coin' => $coin]))
        ->assertOk()
        ->assertSee('Gold coin')
        ->assertSee('Bulgarian series')
        ->assertSee('<p>Description</p>', false);
});

test('coin cards link to their detail pages', function () {
    $coin = Coin::create([
        'title' => ['bg' => 'Тестова монета'],
        'category' => Coin::CATEGORIES[0],
    ]);

    $this->get(route('catalog.index', ['locale' => 'bg']))
        ->assertOk()
        ->assertSee(route('catalog.coin', ['locale' => 'bg', 'coin' => $coin]), false);
});

test('coin detail pages expose localized seo metadata and structured data', function () {
    $coin = Coin::create([
        'title' => ['bg' => 'Златна монета', 'en' => 'Gold coin'],
        'category' => Coin::CATEGORIES[0],
        'description' => ['en' => '<p>Detailed gold coin description.</p>'],
    ]);

    $response = $this->get(route('catalog.coin', ['locale' => 'en', 'coin' => $coin]));

    $response->assertOk()
        ->assertSee('<title>Gold coin | Numis</title>', false)
        ->assertSee('property="og:type" content="website"', false)
        ->assertSee('property="og:url"', false)
        ->assertSee('"@type":"Product"', false)
        ->assertSee(route('catalog.coin', ['locale' => 'en', 'coin' => $coin]), false);
});

test('sitemap includes localized coin detail urls', function () {
    $coin = Coin::create([
        'title' => ['bg' => 'Тестова монета'],
        'category' => Coin::CATEGORIES[0],
    ]);

    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertSee(route('catalog.coin', ['locale' => 'bg', 'coin' => $coin]), false)
        ->assertSee(route('catalog.coin', ['locale' => 'en', 'coin' => $coin]), false)
        ->assertSee(route('catalog.coin', ['locale' => 'de', 'coin' => $coin]), false);
});

test('exchange coins are displayed under their year heading', function () {
    Coin::create([
        'title' => ['bg' => 'Една стотинка'],
        'category' => 'exchange',
        'year' => 2026,
    ]);

    $this->get(route('catalog.index', ['locale' => 'bg', 'category' => 'exchange']))
        ->assertOk()
        ->assertSee('2026')
        ->assertSee('Разменни монети')
        ->assertSee('Една стотинка');
});
