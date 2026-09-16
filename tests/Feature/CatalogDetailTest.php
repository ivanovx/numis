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

test('exchange pages group coins by year and sort by denomination inside each year', function () {
    Coin::create([
        'title' => ['bg' => 'Десет стотинки'],
        'category' => 'exchange',
        'year' => 2026,
        'denomination' => '10 ст.',
    ]);

    Coin::create([
        'title' => ['bg' => 'Една стотинка'],
        'category' => 'exchange',
        'year' => 2026,
        'denomination' => '1 ст.',
    ]);

    Coin::create([
        'title' => ['bg' => 'Пет стотинки'],
        'category' => 'exchange',
        'year' => 2025,
        'denomination' => '5 ст.',
    ]);

    $response = $this->get(route('catalog.index', ['locale' => 'bg', 'category' => 'exchange']));

    $response->assertOk()
        ->assertSee('2026')
        ->assertSee('2025')
        ->assertSeeInOrder(['2026', '1 ст.', '10 ст.', '2025', '5 ст.']);
});

test('category pages show only coins from that category', function () {
    Coin::create([
        'title' => ['bg' => 'Куриозна монета'],
        'category' => 'curiosities',
        'year' => 2027,
    ]);

    Coin::create([
        'title' => ['bg' => 'Разменна монета'],
        'category' => 'exchange',
        'year' => 2027,
    ]);

    $this->get(route('catalog.category', ['locale' => 'bg', 'category' => 'curiosities']))
        ->assertOk()
        ->assertSee('Куриозна монета')
        ->assertDontSee('Разменна монета')
        ->assertDontSee('Филтър');
});

test('main catalog page keeps the filter bar visible', function () {
    $this->get(route('catalog.index', ['locale' => 'bg']))
        ->assertOk()
        ->assertSee('Филтър');
});

test('collectible and commemorative pages group tickets by series and sort by denomination then year', function () {
    $laterSeries = Series::create([
        'name' => ['bg' => 'Серия Б'],
        'slug' => 'series-b',
    ]);

    $earlierSeries = Series::create([
        'name' => ['bg' => 'Серия А'],
        'slug' => 'series-a',
    ]);

    Coin::create([
        'title' => ['bg' => 'Възпоменателна монета 10 лв'],
        'category' => 'commemorative',
        'series_id' => $laterSeries->id,
        'year' => 2025,
        'denomination' => '10 лв',
    ]);

    Coin::create([
        'title' => ['bg' => 'Възпоменателна монета 2 лв'],
        'category' => 'commemorative',
        'series_id' => $earlierSeries->id,
        'year' => 2020,
        'denomination' => '2 лв',
    ]);

    $response = $this->get(route('catalog.category', ['locale' => 'bg', 'category' => 'commemorative']));

    $response->assertOk()
        ->assertSee('Серия А')
        ->assertSee('Серия Б')
        ->assertSeeInOrder(['Серия А', 'Възпоменателна монета 2 лв', 'Серия Б', 'Възпоменателна монета 10 лв']);
});
