<?php

use App\Enums\FuelType;
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

test('purchase entries support diesel and cng fuel types and calculate amount correctly', function () {
    $user = User::factory()->create();
    $pump = Pump::query()->create([
        'name' => 'Fuel Pump',
        'status' => 'active',
    ]);

    $this->actingAs($user)
        ->post(route('purchases.store'), [
            'purchase_date' => today()->toDateString(),
            'pump_id' => $pump->id,
            'slip_number' => 'FUEL-001',
            'fuel_type' => 'diesel',
            'quantity' => 40,
            'rate' => 110,
            'discount_value' => 100,
            'discount_type' => 'taka',
            'bonus_value' => 0,
            'bonus_type' => 'taka',
            'remarks' => 'Diesel purchase',
        ])
        ->assertRedirect(route('purchases.index'));

    $purchase = Purchase::query()->first();

    expect($purchase)->not->toBeNull()
        ->and($purchase->fuel_type)->toBe(FuelType::Diesel)
        ->and((float) $purchase->amount)->toBe(4300.0);
});

test('monthly purchase summary supports fuel type filtering and split totals', function () {
    $pump = Pump::query()->create(['name' => 'Month Pump', 'status' => 'active']);

    Purchase::query()->create([
        'purchase_date' => now()->startOfMonth()->addDays(2),
        'pump_id' => $pump->id,
        'fuel_type' => 'cng',
        'slip_number' => 'CNG-01',
        'quantity' => 10,
        'rate' => 20,
        'amount' => 200,
        'created_by' => User::factory()->create()->id,
    ]);

    Purchase::query()->create([
        'purchase_date' => now()->startOfMonth()->addDays(5),
        'pump_id' => $pump->id,
        'fuel_type' => 'diesel',
        'slip_number' => 'DIESEL-01',
        'quantity' => 5,
        'rate' => 30,
        'amount' => 150,
        'created_by' => User::factory()->create()->id,
    ]);

    $report = app(\App\Services\ReportService::class)->monthlyPurchaseSummary([
        'month' => now()->format('Y-m'),
        'fuel_type' => 'cng',
    ]);

    expect($report['totals']['amount'])->toBe(200.0)
        ->and($report['totals']['cng_amount'])->toBe(200.0)
        ->and($report['totals']['diesel_amount'])->toBe(0.0);
});

test('new purchases default to unsold and can be marked sold in reports totals', function () {
    $pump = Pump::query()->create(['name' => 'Status Pump', 'status' => 'active']);

    $purchase = Purchase::query()->create([
        'purchase_date' => today(),
        'pump_id' => $pump->id,
        'slip_number' => 'STATUS-01',
        'fuel_type' => 'cng',
        'quantity' => 12,
        'rate' => 25,
        'amount' => 300,
        'created_by' => User::factory()->create()->id,
    ]);

    expect($purchase->status->value)->toBe('unsold');

    $purchase->status = \App\Enums\PurchaseStatus::Sold;
    $purchase->save();

    $report = app(\App\Services\ReportService::class)->dailyPurchases(['status' => 'sold']);

    expect($report['totals']['count'])->toBe(1)
        ->and($report['totals']['sold_count'])->toBe(1)
        ->and($report['totals']['unsold_count'])->toBe(0);
});

test('purchase status can be toggled from the purchase table action', function () {
    $user = User::factory()->create();
    $pump = Pump::query()->create(['name' => 'Toggle Pump', 'status' => 'active']);
    $purchase = Purchase::query()->create([
        'purchase_date' => today(),
        'pump_id' => $pump->id,
        'slip_number' => 'TOGGLE-01',
        'fuel_type' => 'cng',
        'quantity' => 1,
        'rate' => 25,
        'amount' => 25,
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->patch(route('purchases.toggle-status', $purchase))
        ->assertRedirect(route('purchases.index'));

    expect($purchase->fresh()->status->value)->toBe('sold');

    $this->actingAs($user)
        ->patch(route('purchases.toggle-status', $purchase), ['status' => 'unsold'])
        ->assertRedirect(route('purchases.index'));

    expect($purchase->fresh()->status->value)->toBe('sold');
});
