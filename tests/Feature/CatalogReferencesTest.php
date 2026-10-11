<?php

use App\Models\Coin;

function referencedCoin(array $overrides = []): Coin
{
    return Coin::create(array_merge([
        'title' => ['bg' => 'Златна монета', 'en' => 'Gold coin'],
        'category' => Coin::CATEGORIES[0],
        'km_number' => 'KM# 202',
        'numista_number' => 'N# 555',
        'bnb_number' => 'NC_2025_9_100LV',
        'other_references' => 'Schön 12',
    ], $overrides));
}

test('catalog numbers are shown on the public detail page', function () {
    $coin = referencedCoin();

    $this->get(route('catalog.coin', ['locale' => 'bg', 'coin' => $coin]))
        ->assertOk()
        ->assertSee('Каталожни номера')
        ->assertSee('KM# 202')
        ->assertSee('N# 555')
        ->assertSee('NC_2025_9_100LV')
        ->assertSee('Schön 12');
});

test('the bnb and numista links follow the visitor language', function () {
    $coin = referencedCoin();
    $base = 'https://www.bnb.bg/NotesAndCoins/NACCommemorative/NACCommemorativeEmissions/NC_2025_9_100LV';

    $this->get(route('catalog.coin', ['locale' => 'bg', 'coin' => $coin]))
        ->assertSee($base.'_BG', false)
        ->assertSee('https://en.numista.com/555', false);

    $this->get(route('catalog.coin', ['locale' => 'en', 'coin' => $coin]))
        ->assertSee($base.'_EN', false);

    $this->get(route('catalog.coin', ['locale' => 'de', 'coin' => $coin]))
        ->assertSee($base.'_EN', false)
        ->assertSee('https://de.numista.com/555', false);
});

test('the references section is hidden when a coin has no catalog numbers', function () {
    $coin = Coin::create(['title' => ['bg' => 'Проста монета'], 'category' => Coin::CATEGORIES[0]]);

    $this->get(route('catalog.coin', ['locale' => 'bg', 'coin' => $coin]))
        ->assertOk()
        ->assertDontSee('Каталожни номера');
});

test('catalog numbers are exposed as structured data', function () {
    $coin = referencedCoin();

    $this->get(route('catalog.coin', ['locale' => 'en', 'coin' => $coin]))
        ->assertOk()
        ->assertSee('"propertyID":"Krause-Mishler KM#"', false)
        ->assertSee('"value":"KM# 202"', false)
        ->assertSee('"sameAs"', false);
});

test('a bnb code that is not an emission code gets no link', function () {
    $coin = referencedCoin(['bnb_number' => 'SOMETHING_ELSE']);

    expect($coin->bnbUrl('bg'))->toBeNull();
});

test('collectors can search the catalog by krause number', function () {
    referencedCoin(['title' => ['bg' => 'Христо Ботев'], 'km_number' => '202']);
    referencedCoin(['title' => ['bg' => 'Иван Вазов'], 'km_number' => '1202', 'bnb_number' => 'NC_2025_7_10LV']);

    // As written in catalogs: "KM# 202" finds KM# 202 — not KM# 1202.
    $this->get(route('catalog.index', ['locale' => 'bg', 'q' => 'KM# 202']))
        ->assertOk()
        ->assertSee('Христо Ботев')
        ->assertDontSee('Иван Вазов');

    $this->get(route('catalog.index', ['locale' => 'bg', 'q' => 'km 1202']))
        ->assertSee('Иван Вазов')
        ->assertDontSee('Христо Ботев');
});

test('search ignores letter case, including cyrillic', function () {
    referencedCoin(['title' => ['bg' => 'Христо Ботев'], 'km_number' => null]);

    $this->get(route('catalog.index', ['locale' => 'bg', 'q' => 'ботев']))
        ->assertSee('Христо Ботев');

    $this->get(route('catalog.index', ['locale' => 'bg', 'q' => 'ХРИСТО']))
        ->assertSee('Христо Ботев');
});

test('search treats underscores in bnb codes literally', function () {
    referencedCoin(['title' => ['bg' => 'Първа'], 'km_number' => null, 'bnb_number' => 'NC_2025_7_10LV']);
    referencedCoin(['title' => ['bg' => 'Втора'], 'km_number' => null, 'bnb_number' => 'NCX2025X7X10LV']);

    $this->get(route('catalog.index', ['locale' => 'bg', 'q' => 'NC_2025_7']))
        ->assertSee('Първа')
        ->assertDontSee('Втора');
});

test('special characters in a search do not break the page', function () {
    referencedCoin();

    foreach (['100%', '50_', "O'Brien", '"quoted"', '\\', '%%', '!'] as $term) {
        $this->get(route('catalog.index', ['locale' => 'bg', 'q' => $term]))->assertOk();
    }
});

test('the metal filter finds variants of a base metal', function () {
    referencedCoin(['title' => ['bg' => 'Обикновена'], 'metal' => 'Ag 925/1000']);
    referencedCoin(['title' => ['bg' => 'Позлатена'], 'metal' => 'Ag 925/1000 с частично позлатяване']);
    referencedCoin(['title' => ['bg' => 'Златна'], 'metal' => 'Au 999/1000']);

    $this->get(route('catalog.index', ['locale' => 'bg', 'metal' => 'Ag 925/1000']))
        ->assertSee('Обикновена')
        ->assertSee('Позлатена')
        ->assertDontSee('Златна');

    $this->get(route('catalog.index', ['locale' => 'bg', 'metal' => 'Ag 925/1000 с частично позлатяване']))
        ->assertSee('Позлатена')
        ->assertDontSee('Обикновена');
});
