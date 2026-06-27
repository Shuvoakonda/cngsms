<?php

use App\Models\Driver;
use App\Models\Payment;
use App\Models\Pump;
use App\Models\Purchase;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dashboard page renders for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Recent Transactions')
        ->assertSee('Monthly Purchase vs Payment');
});

test('dashboard service calculates today totals and outstanding', function () {
    $user = User::factory()->create();
    $driver = Driver::query()->create(['name' => 'Test Driver']);
    $vehicle = Vehicle::query()->create([
        'vehicle_number' => 'TEST-1',
        'driver_id' => $driver->id,
        'status' => 'active',
    ]);
    $pump = Pump::query()->create([
        'name' => 'Test Pump',
        'opening_balance' => 1000,
        'credit_limit' => 5000,
        'status' => 'active',
    ]);

    Purchase::query()->create([
        'purchase_date' => today(),
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
        'pump_id' => $pump->id,
        'slip_number' => 'SLIP-1',
        'quantity' => 10,
        'rate' => 100,
        'amount' => 1000,
        'created_by' => $user->id,
    ]);

    Payment::query()->create([
        'payment_date' => today(),
        'pump_id' => $pump->id,
        'voucher_number' => 'PV-1',
        'payment_method' => 'cash',
        'amount' => 400,
        'created_by' => $user->id,
    ]);

    $stats = app(DashboardService::class)->stats();

    expect($stats['today_purchase'])->toBe(1000.0)
        ->and($stats['today_payment'])->toBe(400.0)
        ->and($stats['total_outstanding'])->toBe(1600.0);
});
