<?php

use App\Models\User;

test('authenticated users can visit the coins grid', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('admin.coins.index'));

    $response->assertOk();
    $response->assertSee('Search title, series, metal...');
    $response->assertSee(route('admin.coins.create'));
});
