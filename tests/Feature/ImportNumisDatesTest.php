<?php

use App\Models\Coin;

/** Writes a dates JSON file and returns its path. */
function datesFile(array $rows): string
{
    $path = tempnam(sys_get_temp_dir(), 'numis_dates_');
    file_put_contents($path, json_encode($rows, JSON_UNESCAPED_UNICODE));

    return $path;
}

function evtimiyRow(array $overrides = []): array
{
    return array_merge([
        'year' => 2025,
        'title' => 'Св. Патриарх Евтимий Търновски',
        'date' => '2025-11-24',
        'bnb_code' => 'NC_2025_9_100LV',
    ], $overrides);
}

test('numis:import_dates is a dry run by default', function () {
    $coin = Coin::create(['title' => ['bg' => 'Св. Патриарх Евтимий Търновски'], 'year' => 2025, 'category' => Coin::CATEGORIES[0]]);

    $this->artisan('numis:import_dates', ['path' => datesFile([evtimiyRow()])])->assertExitCode(0);

    expect($coin->fresh()->issue_date)->toBeNull()
        ->and($coin->fresh()->bnb_number)->toBeNull();
});

test('numis:import_dates --apply fills the issue date and the bnb code', function () {
    $coin = Coin::create(['title' => ['bg' => 'Св. Патриарх Евтимий Търновски'], 'year' => 2025, 'category' => Coin::CATEGORIES[0]]);

    $this->artisan('numis:import_dates', ['path' => datesFile([evtimiyRow()]), '--apply' => true])->assertExitCode(0);

    expect($coin->fresh()->issue_date->format('Y-m-d'))->toBe('2025-11-24')
        ->and($coin->fresh()->bnb_number)->toBe('NC_2025_9_100LV');
});

test('numis:import_dates matches titles that carry an extra prefix', function () {
    $coin = Coin::create(['title' => ['bg' => 'Българска иконография • Св. Патриарх Евтимий Търновски'], 'year' => 2025, 'category' => Coin::CATEGORIES[0]]);

    $this->artisan('numis:import_dates', ['path' => datesFile([evtimiyRow()]), '--apply' => true]);

    expect($coin->fresh()->issue_date?->format('Y-m-d'))->toBe('2025-11-24');
});

test('numis:import_dates keeps existing values unless --force is given', function () {
    $coin = Coin::create([
        'title' => ['bg' => 'Св. Патриарх Евтимий Търновски'],
        'year' => 2025,
        'category' => Coin::CATEGORIES[0],
        'issue_date' => '2025-01-01',
        'bnb_number' => 'MANUAL_CODE',
    ]);

    $path = datesFile([evtimiyRow()]);

    $this->artisan('numis:import_dates', ['path' => $path, '--apply' => true]);

    expect($coin->fresh()->issue_date->format('Y-m-d'))->toBe('2025-01-01')
        ->and($coin->fresh()->bnb_number)->toBe('MANUAL_CODE');

    $this->artisan('numis:import_dates', ['path' => $path, '--apply' => true, '--force' => true]);

    expect($coin->fresh()->issue_date->format('Y-m-d'))->toBe('2025-11-24')
        ->and($coin->fresh()->bnb_number)->toBe('NC_2025_9_100LV');
});

test('numis:import_dates only fills the missing bnb code when the date is already set', function () {
    $coin = Coin::create([
        'title' => ['bg' => 'Св. Патриарх Евтимий Търновски'],
        'year' => 2025,
        'category' => Coin::CATEGORIES[0],
        'issue_date' => '2025-11-24',
    ]);

    $this->artisan('numis:import_dates', ['path' => datesFile([evtimiyRow()]), '--apply' => true]);

    expect($coin->fresh()->bnb_number)->toBe('NC_2025_9_100LV');
});

test('numis:import_dates uses the denomination to tell identical titles apart', function () {
    $gold100 = Coin::create(['title' => ['bg' => 'Св. Богородица – Златна ябълка'], 'year' => 2025, 'denomination' => '100 лева', 'category' => Coin::CATEGORIES[0]]);
    $gold10 = Coin::create(['title' => ['bg' => 'Св. Богородица – Златна ябълка'], 'year' => 2025, 'denomination' => '10 лева', 'category' => Coin::CATEGORIES[0]]);

    $rows = [[
        'year' => 2025,
        'title' => 'Св. Богородица – Златна ябълка',
        'denomination' => '100 лева',
        'date' => '2025-01-02',
        'bnb_code' => 'NC_2025_4_100LV',
    ]];

    $this->artisan('numis:import_dates', ['path' => datesFile($rows), '--apply' => true]);

    expect($gold100->fresh()->bnb_number)->toBe('NC_2025_4_100LV')
        ->and($gold10->fresh()->bnb_number)->toBeNull();
});

test('numis:import_dates never guesses between equally good matches', function () {
    $a = Coin::create(['title' => ['bg' => 'Златна монета'], 'year' => 2020, 'category' => Coin::CATEGORIES[0]]);
    $b = Coin::create(['title' => ['bg' => 'Златна монета'], 'year' => 2020, 'category' => Coin::CATEGORIES[0]]);

    $rows = [['year' => 2020, 'title' => 'Златна монета', 'date' => '2020-05-05', 'bnb_code' => 'NC_2020_1_10LV']];

    $this->artisan('numis:import_dates', ['path' => datesFile($rows), '--apply' => true]);

    expect($a->fresh()->issue_date)->toBeNull()
        ->and($b->fresh()->issue_date)->toBeNull();
});

test('numis:import_dates ignores coins from other years', function () {
    $coin = Coin::create(['title' => ['bg' => 'Св. Патриарх Евтимий Търновски'], 'year' => 2019, 'category' => Coin::CATEGORIES[0]]);

    $this->artisan('numis:import_dates', ['path' => datesFile([evtimiyRow()]), '--apply' => true]);

    expect($coin->fresh()->issue_date)->toBeNull();
});

test('the bundled dates file is valid and every entry has a date and a bnb code', function () {
    $rows = json_decode(file_get_contents(database_path('data/numis_dates_import.json')), true);

    expect($rows)->toBeArray()->not->toBeEmpty();

    foreach ($rows as $row) {
        expect($row['year'])->toBeInt()
            ->and($row['title'])->not->toBeEmpty()
            ->and($row['date'])->toMatch('/^\d{4}-\d{2}-\d{2}$/')
            ->and(substr($row['date'], 0, 4))->toBe((string) $row['year'])
            ->and($row['bnb_code'])->toMatch('/^NC_[A-Z0-9_]+$/');
    }

    $codes = array_column($rows, 'bnb_code');
    expect(count($codes))->toBe(count(array_unique($codes)));
});
