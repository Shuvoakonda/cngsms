<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using username', function () {
    $user = User::factory()->create([
        'username' => 'staff1',
        'password' => 'secret-pass',
    ]);

    $response = $this->post('/login', [
        'username' => 'staff1',
        'password' => 'secret-pass',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('inactive users cannot authenticate', function () {
    $user = User::factory()->inactive()->create([
        'username' => 'inactive-user',
        'password' => 'secret-pass',
    ]);

    $this->post('/login', [
        'username' => 'inactive-user',
        'password' => 'secret-pass',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('guests are redirected from dashboard', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users can visit dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Dashboard');
});
