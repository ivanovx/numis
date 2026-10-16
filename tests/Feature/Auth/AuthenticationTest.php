<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $this->get(route('login'))->assertOk();
});

test('an authenticated user is redirected away from the login screen', function () {
    // The `guest` middleware on the login route sends signed-in users to the home page.
    $this->actingAs(User::factory()->create())
        ->get(route('login'))
        ->assertRedirect(route('home'));
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('users are sent back to the page they originally asked for', function () {
    $user = User::factory()->create();

    $this->withSession(['url.intended' => route('admin.coins.index')])
        ->post(route('login'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('admin.coins.index'));
});

test('users can not authenticate with an invalid password', function () {
    $user = User::factory()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('the login form requires an email and a password', function () {
    $this->post(route('login'), [])->assertSessionHasErrors(['email', 'password']);

    $this->assertGuest();
});

test('guests can not open the admin panel', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    $this->get(route('admin.coins.index'))->assertRedirect(route('login'));
});

test('users can logout', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
