<?php

use App\Models\Coin;
use App\Models\Series;

it('exposes a graphql endpoint for coin data', function () {
    $series = Series::create([
        'name' => ['bg' => 'Сребро', 'en' => 'Silver'],
        'slug' => 'silver',
    ]);

    Coin::create([
        'title' => ['bg' => 'Левски', 'en' => 'Levsky'],
        'series_id' => $series->id,
        'year' => 2025,
        'metal' => 'Сребро',
        'description' => ['bg' => 'Описание', 'en' => 'Description'],
    ]);

    $response = $this->postJson('/graphql', [
        'query' => '{
            coins {
                id
                title
                seriesName
            }
        }',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.coins.0.title', 'Левски');
});
