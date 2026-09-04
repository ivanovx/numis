<?php

use App\Models\Artist;
use App\Models\Coin;
use App\Models\Series;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('admin can export coins as csv', function () {
    $this->actingAs(User::factory()->create());
    Coin::create(['title' => ['bg' => 'Тестова монета'], 'category' => Coin::CATEGORIES[0]]);

    $response = $this->get(route('admin.csv.export', 'coins'));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($response->streamedContent())->toContain('title_bg', 'Тестова монета');
});

test('admin can import series and artists from csv', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.csv.import', 'series'), [
        'csv' => UploadedFile::fake()->createWithContent('series.csv', "name_bg,name_en,name_de,slug\nБългарски серии,Bulgarian series,Bulgarische Serien,bulgarian-series\n"),
    ])->assertRedirect();

    $this->post(route('admin.csv.import', 'artists'), [
        'csv' => UploadedFile::fake()->createWithContent('artists.csv', "name,slug\nИван Вазов,ivan-vazov\n"),
    ])->assertRedirect();

    expect(Series::where('slug', 'bulgarian-series')->exists())->toBeTrue()
        ->and(Artist::where('slug', 'ivan-vazov')->exists())->toBeTrue();
});

test('admin can import coins with series and artist relationships from csv', function () {
    $this->actingAs(User::factory()->create());
    Series::create(['name' => ['bg' => 'Български серии'], 'slug' => 'bulgarian-series']);
    Artist::create(['name' => 'Иван Вазов', 'slug' => 'ivan-vazov']);

    $headers = ['id', 'title_bg', 'title_en', 'title_de', 'series_slug', 'artist_slugs', 'category', 'year', 'issue_date', 'denomination', 'metal', 'quality', 'weight', 'diameter', 'mintage', 'edge_bg', 'edge_en', 'edge_de', 'mint_bg', 'mint_en', 'mint_de', 'front_image', 'front_description_bg', 'front_description_en', 'front_description_de', 'back_image', 'back_description_bg', 'back_description_en', 'back_description_de', 'description_bg', 'description_en', 'description_de'];
    $values = ['', 'Тестова монета', '', '', 'bulgarian-series', 'ivan-vazov', 'commemorative', '2026', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
    $csv = implode(',', $headers)."\n".implode(',', $values)."\n";

    $this->post(route('admin.csv.import', 'coins'), [
        'csv' => UploadedFile::fake()->createWithContent('coins.csv', $csv),
    ])->assertRedirect();

    $coin = Coin::where('year', 2026)->firstOrFail();

    expect($coin->series?->slug)->toBe('bulgarian-series')
        ->and($coin->artists->first()?->slug)->toBe('ivan-vazov');
});
