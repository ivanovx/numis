<?php

use App\Models\Coin;
use App\Models\User;

test('authenticated users can visit the coins grid', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('admin.coins.index'));

    $response->assertOk();
    $response->assertSee('Search title, series, metal...');
    $response->assertSee(route('admin.coins.create'));
});

test('coins grid shows missing data statuses', function () {
    $this->actingAs(User::factory()->create());
    Coin::create([
        'title' => ['bg' => 'Непълна монета'],
        'category' => Coin::CATEGORIES[0],
    ]);

    $response = $this->get(route('admin.coins.index'));

    $response->assertOk()
        ->assertSee('Missing images')
        ->assertSee('Missing translations')
        ->assertSee('Missing series')
        ->assertSee('Missing year')
        ->assertSee('Missing description');
});
