<?php

use App\Models\Coin;
use App\Models\Period;
use App\Models\User;
use Illuminate\Support\Str;

test('authenticated users can manage historical periods', function () {
    $this->actingAs(User::factory()->create());

    $name = 'Царство България при Фердинанд';
    $response = $this->post(route('admin.periods.store'), [
        'name' => $name,
        'slug' => '',
    ]);

    $period = Period::firstOrFail();

    $response->assertRedirect(route('admin.periods.index'));
    expect($period->name)->toBe($name)
        ->and($period->slug)->toBe(Str::slug($name));

    $this->get(route('admin.periods.index'))
        ->assertOk()
        ->assertSee($name)
        ->assertSee(route('admin.periods.edit', $period));

    $this->put(route('admin.periods.update', $period), [
        'name' => 'Народна република',
        'slug' => 'narodna-republika',
    ])->assertRedirect(route('admin.periods.index'));

    expect($period->fresh()->name)->toBe('Народна република');
});

test('period validation messages are displayed in Bulgarian', function () {
    $this->actingAs(User::factory()->create());

    $this->from(route('admin.periods.create'))
        ->post(route('admin.periods.store'), ['name' => ''])
        ->assertRedirect(route('admin.periods.create'))
        ->assertSessionHasErrors(['name' => 'Полето име е задължително.']);
});

test('deleting a period leaves its coins unassigned', function () {
    $this->actingAs(User::factory()->create());

    $period = Period::create(['name' => 'A historical period', 'slug' => 'a-historical-period']);
    $coin = Coin::create([
        'title' => ['bg' => 'Coin'],
        'category' => Coin::CATEGORIES[0],
        'period_id' => $period->id,
    ]);

    $this->delete(route('admin.periods.destroy', $period))
        ->assertRedirect(route('admin.periods.index'));

    expect($coin->fresh()->period_id)->toBeNull()
        ->and(Period::find($period->id))->toBeNull();
});

test('period slugs are unique after automatic generation', function () {
    $this->actingAs(User::factory()->create());

    Period::create(['name' => 'Народна република', 'slug' => 'narodna-republika']);

    $this->from(route('admin.periods.create'))
        ->post(route('admin.periods.store'), [
            'name' => 'Народна република',
            'slug' => '',
        ])
        ->assertRedirect(route('admin.periods.create'))
        ->assertSessionHasErrors('slug');

    expect(Period::count())->toBe(1);
});

test('coin create and edit forms expose the historical period', function () {
    $this->actingAs(User::factory()->create());

    $period = Period::create(['name' => 'Народна република', 'slug' => 'narodna-republika']);
    $coin = Coin::create([
        'title' => ['bg' => 'Coin'],
        'category' => Coin::CATEGORIES[0],
        'period_id' => $period->id,
    ]);

    $this->get(route('admin.coins.create'))
        ->assertOk()
        ->assertSee($period->name);

    $this->get(route('admin.coins.edit', $coin))
        ->assertOk()
        ->assertSee($period->name)
        ->assertSee('value="'.$period->id.'" selected', false);
});
