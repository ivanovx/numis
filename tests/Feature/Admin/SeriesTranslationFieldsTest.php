<?php

use App\Models\Series;
use App\Models\User;

test('series creation shows only Bulgarian translation fields', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('admin.series.create'));

    $response->assertOk();
    $response->assertSee('name="name[bg]"', false);
    $response->assertDontSee('name="name[en]"', false);
    $response->assertDontSee('name="name[de]"', false);
});

test('series editing shows all translation fields', function () {
    $this->actingAs(User::factory()->create());

    $series = Series::create([
        'name' => ['bg' => 'Български серии'],
        'slug' => 'bulgarian-series',
    ]);

    $response = $this->get(route('admin.series.edit', $series));

    $response->assertOk();
    $response->assertSee('name="name[bg]"', false);
    $response->assertSee('name="name[en]"', false);
    $response->assertSee('name="name[de]"', false);
});
