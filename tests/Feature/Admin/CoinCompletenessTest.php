<?php

use App\Models\Artist;
use App\Models\Coin;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

/** A coin with every tracked completeness field filled in. */
function completeCoin(array $overrides = []): Coin
{
    $coin = Coin::create(array_merge([
        'title' => ['bg' => 'Пълна монета'],
        'category' => Coin::CATEGORIES[0],
        'year' => 2025,
        'metal' => 'Ag 999/1000',
        'weight' => '23.33',
        'diameter' => '38.61',
        'mintage' => '4000',
        'edge' => ['bg' => 'гладък'],
        'mint' => ['bg' => 'Монетен двор'],
        'issue_date' => '2025-04-28',
        'km_number' => '202',
    ], $overrides));

    $coin->artists()->attach(Artist::create(['name' => 'Автор', 'slug' => 'avtor-'.uniqid()]));

    return $coin->load('artists');
}

function emptyCoin(string $title = 'Празна монета'): Coin
{
    return Coin::create(['title' => ['bg' => $title], 'category' => Coin::CATEGORIES[0]])->load('artists');
}

test('a complete coin scores every tracked field', function () {
    $completeness = completeCoin()->completeness();

    expect($completeness['filled'])->toBe(9)
        ->and($completeness['total'])->toBe(9)
        ->and($completeness['percent'])->toBe(100)
        ->and($completeness['missing'])->toBe([]);
});

test('an empty coin reports every tracked field as missing', function () {
    $completeness = emptyCoin()->completeness();

    expect($completeness['filled'])->toBe(0)
        ->and(array_keys($completeness['missing']))->toBe(array_keys(Coin::COMPLETENESS_FIELDS));
});

test('blank values do not count as filled', function () {
    $coin = Coin::create([
        'title' => ['bg' => 'Монета'],
        'category' => Coin::CATEGORIES[0],
        'weight' => '   ',
    ])->load('artists');

    expect($coin->completeness()['missing'])->toHaveKey('weight');
});

test('translated fields count as filled when any language has text', function () {
    $coin = Coin::create([
        'title' => ['bg' => 'Монета'],
        'category' => Coin::CATEGORIES[0],
        'edge' => ['en' => 'plain'],
    ])->load('artists');

    expect($coin->completeness()['missing'])->not->toHaveKey('edge');
});

test('the sql scopes agree with the php indicator', function () {
    $full = completeCoin();
    $empty = emptyCoin();
    $partial = Coin::create(['title' => ['bg' => 'Частична'], 'category' => Coin::CATEGORIES[0], 'weight' => '10']);

    expect(Coin::completenessState('complete')->pluck('id')->all())->toBe([$full->id])
        ->and(Coin::completenessState('incomplete')->count())->toBe(2)
        ->and(Coin::missingField('weight')->pluck('id')->sort()->values()->all())->toBe([$empty->id])
        ->and(Coin::missingField('km_number')->count())->toBe(2)
        ->and(Coin::orderByCompleteness('asc')->first()->id)->toBe($empty->id)
        ->and(Coin::orderByCompleteness('desc')->first()->id)->toBe($full->id)
        ->and($partial->load('artists')->completeness()['filled'])->toBe(1);
});

test('unknown missing-field names are ignored instead of breaking the query', function () {
    emptyCoin();

    expect(Coin::missingField('not_a_field; DROP TABLE coins')->count())->toBe(1);
});

test('the completeness report summarises the whole catalog', function () {
    completeCoin();
    emptyCoin('Една');
    emptyCoin('Две');

    $report = Coin::completenessReport();

    expect($report['total'])->toBe(3)
        ->and($report['complete'])->toBe(1)
        ->and($report['missing']['km_number'])->toBe(2)
        ->and($report['missing']['weight'])->toBe(2)
        ->and($report['average_percent'])->toBe((int) round(9 / 27 * 100));
});

test('the completeness report handles an empty catalog', function () {
    expect(Coin::completenessReport())->toMatchArray(['total' => 0, 'average_percent' => 0, 'complete' => 0]);
});

test('coins grid shows the data completeness of each coin', function () {
    $this->actingAs(User::factory()->create());
    completeCoin();
    emptyCoin();

    $this->get(route('admin.coins.index'))
        ->assertOk()
        ->assertSee('data-completeness="9/9"', false)
        ->assertSee('data-completeness="0/9"', false);
});

test('coins grid can show only incomplete coins', function () {
    $this->actingAs(User::factory()->create());
    completeCoin();
    emptyCoin('Непълна монета');

    Livewire::test('admin.coins-grid')
        ->set('completeness', 'incomplete')
        ->assertSee('Непълна монета')
        ->assertDontSee('Пълна монета');
});

test('coins grid can filter by a missing field', function () {
    $this->actingAs(User::factory()->create());
    completeCoin();
    emptyCoin('Без каталожен номер');

    Livewire::test('admin.coins-grid')
        ->set('missing', 'km_number')
        ->assertSee('Без каталожен номер')
        ->assertDontSee('Пълна монета');
});

test('coins grid search also finds coins by catalog number', function () {
    $this->actingAs(User::factory()->create());
    completeCoin(['km_number' => '777']);
    emptyCoin('Друга монета');

    Livewire::test('admin.coins-grid')
        ->set('search', '777')
        ->assertSee('Пълна монета')
        ->assertDontSee('Друга монета');
});

test('the coin edit form shows catalog reference fields and the completeness panel', function () {
    $this->actingAs(User::factory()->create());
    $coin = completeCoin(['km_number' => '202.1']);

    $this->get(route('admin.coins.edit', $coin))
        ->assertOk()
        ->assertSee('data-completeness="9/9"', false)
        ->assertSee('name="km_number"', false)
        ->assertSee('name="numista_number"', false)
        ->assertSee('name="bnb_number"', false)
        ->assertSee('name="other_references"', false)
        ->assertSee('value="202.1"', false);
});

test('catalog references are normalised and saved from the admin form', function () {
    $this->actingAs(User::factory()->create());
    $coin = emptyCoin();

    $this->put(route('admin.coins.update', $coin), [
        'title' => ['bg' => 'Монета'],
        'category' => Coin::CATEGORIES[0],
        'km_number' => 'KM# 202.1',
        'numista_number' => 'https://en.numista.com/catalogue/pieces12345.html',
        'bnb_number' => 'https://www.bnb.bg/NotesAndCoins/NACCommemorative/NACCommemorativeEmissions/NC_2025_9_100LV_BG',
        'other_references' => ' Schön 12 ',
    ])->assertRedirect(route('admin.coins.index'));

    $coin = $coin->fresh();

    expect($coin->km_number)->toBe('202.1')
        ->and($coin->numista_number)->toBe('12345')
        ->and($coin->bnb_number)->toBe('NC_2025_9_100LV')
        ->and($coin->other_references)->toBe('Schön 12');
});

test('the admin dashboard summarises data completeness', function () {
    $this->actingAs(User::factory()->create());
    completeCoin();
    emptyCoin();

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Пълнота на данните')
        ->assertSee('1 от 2 монети');
});

test('csv export includes the catalog reference columns', function () {
    $this->actingAs(User::factory()->create());
    completeCoin(['km_number' => '202']);

    $content = $this->get(route('admin.csv.export', 'coins'))->streamedContent();

    expect($content)->toContain('km_number', 'bnb_number', 'numista_number', 'other_references');
});

/** Builds a coins CSV with every column the importer reads (optionally plus the reference columns). */
function coinCsv(array $row, bool $withReferences = false): string
{
    $headers = ['id', 'title_bg', 'title_en', 'title_de', 'series_slug', 'artist_slugs', 'category', 'year', 'issue_date', 'denomination', 'metal', 'quality', 'weight', 'diameter', 'mintage', 'edge_bg', 'edge_en', 'edge_de', 'mint_bg', 'mint_en', 'mint_de', 'front_image', 'front_description_bg', 'front_description_en', 'front_description_de', 'back_image', 'back_description_bg', 'back_description_en', 'back_description_de', 'description_bg', 'description_en', 'description_de'];

    if ($withReferences) {
        $headers = array_merge($headers, ['km_number', 'bnb_number', 'numista_number', 'other_references']);
    }

    return implode(',', $headers)."\n".implode(',', array_map(fn (string $header) => $row[$header] ?? '', $headers))."\n";
}

test('importing an older csv without reference columns keeps existing catalog numbers', function () {
    $this->actingAs(User::factory()->create());
    $coin = completeCoin(['km_number' => '202', 'bnb_number' => 'NC_2025_5_10LV']);

    $csv = coinCsv(['id' => $coin->id, 'title_bg' => 'Преименувана монета', 'category' => Coin::CATEGORIES[0], 'year' => '2025']);

    $this->post(route('admin.csv.import', 'coins'), [
        'csv' => UploadedFile::fake()->createWithContent('coins.csv', $csv),
    ])->assertRedirect()->assertSessionHasNoErrors();

    $coin = $coin->fresh();

    // The import really ran (the title changed) …
    expect($coin->translation('title', 'bg'))->toBe('Преименувана монета')
        // … and it did not wipe the numbers entered in the admin.
        ->and($coin->km_number)->toBe('202')
        ->and($coin->bnb_number)->toBe('NC_2025_5_10LV');
});

test('importing a csv with reference columns sets and normalises them', function () {
    $this->actingAs(User::factory()->create());

    $csv = coinCsv([
        'title_bg' => 'Монета от CSV',
        'category' => Coin::CATEGORIES[0],
        'km_number' => 'KM# 55',
        'bnb_number' => 'nc_2020_1_2lv_bg',
        'numista_number' => 'N#9',
    ], withReferences: true);

    $this->post(route('admin.csv.import', 'coins'), [
        'csv' => UploadedFile::fake()->createWithContent('coins.csv', $csv),
    ])->assertRedirect()->assertSessionHasNoErrors();

    $coin = Coin::where('km_number', '55')->firstOrFail();

    expect($coin->bnb_number)->toBe('NC_2020_1_2LV')
        ->and($coin->numista_number)->toBe('9')
        ->and($coin->other_references)->toBeNull();
});
