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
        'locale' => 'bg',
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

it('exposes technical data and catalog numbers', function () {
    Coin::create([
        'title' => ['bg' => 'Ботев'],
        'year' => 2023,
        'issue_date' => '2023-01-03',
        'weight' => '23.33',
        'diameter' => '38.61',
        'mintage' => '4000',
        'edge' => ['bg' => 'гладък'],
        'mint' => ['bg' => 'Монетен двор'],
        'km_number' => 'KM# 202',
        'bnb_number' => 'NC_2023_1_10LV',
    ]);

    $response = $this->postJson('/graphql', [
        'locale' => 'bg',
        'query' => '{
            coins {
                issue_date
                weight
                diameter
                mintage
                edge
                mint
                km_number
                bnb_number
            }
        }',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.coins.0.issue_date', '2023-01-03')
        ->assertJsonPath('data.coins.0.weight', '23.33')
        ->assertJsonPath('data.coins.0.edge', 'гладък')
        ->assertJsonPath('data.coins.0.mint', 'Монетен двор')
        ->assertJsonPath('data.coins.0.km_number', '202')
        ->assertJsonPath('data.coins.0.bnb_number', 'NC_2023_1_10LV');
});
