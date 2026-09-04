<?php

use App\Models\Coin;
use App\Models\User;

test('coin creation shows only Bulgarian translation fields', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('admin.coins.create'));

    $response->assertOk();
    $response->assertSee('name="title[bg]"', false);
    $response->assertDontSee('name="title[en]"', false);
    $response->assertDontSee('name="title[de]"', false);
});

test('coin editing shows all translation fields', function () {
    $this->actingAs(User::factory()->create());

    $coin = Coin::create([
        'title' => ['bg' => 'Златна монета'],
        'category' => Coin::CATEGORIES[0],
    ]);

    $response = $this->get(route('admin.coins.edit', $coin));

    $response->assertOk();
    $response->assertSee('name="title[bg]"', false);
    $response->assertSee('name="title[en]"', false);
    $response->assertSee('name="title[de]"', false);
});

test('coin description fields use rich text editors', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('admin.coins.create'));

    $response->assertSee('data-rich-text-editor="description-bg"', false);
    $response->assertSee('data-rich-text-editor="front-description-bg"', false);
    $response->assertSee('data-rich-text-editor="back-description-bg"', false);
});

test('unsafe markup is removed from coin descriptions on update', function () {
    $this->actingAs(User::factory()->create());

    $coin = Coin::create([
        'title' => ['bg' => 'Златна монета'],
        'category' => Coin::CATEGORIES[0],
    ]);

    $this->put(route('admin.coins.update', $coin), [
        'title' => ['bg' => 'Златна монета'],
        'category' => Coin::CATEGORIES[0],
        'description' => ['bg' => '<p><strong>Описание</strong></p><script>alert(1)</script>'],
    ])->assertRedirect(route('admin.coins.index'));

    expect($coin->fresh()->translation('description', 'bg'))->toBe('<p><strong>Описание</strong></p>alert(1)');
});
