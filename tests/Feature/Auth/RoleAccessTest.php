<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('administrator can access settings page', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get(route('admin.settings'))
        ->assertOk()
        ->assertSee('System Settings');
});

test('data entry user cannot access settings page', function () {
    $user = User::factory()->dataEntry()->create();

    $this->actingAs($user)
        ->get(route('admin.settings'))
        ->assertForbidden();
});

test('administrator can delete records according to model helper', function () {
    $admin = User::factory()->administrator()->create();

    expect($admin->canDeleteRecords())->toBeTrue();
});

test('data entry user cannot delete records according to model helper', function () {
    $user = User::factory()->dataEntry()->create();

    expect($user->canDeleteRecords())->toBeFalse();
});

test('inactive authenticated user is logged out on next request', function () {
    $user = User::factory()->inactive()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('login'));
});
