<?php

use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\PumpController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/search', SearchController::class)->name('search');

    Route::resource('purchases', PurchaseController::class)->except(['show', 'create', 'edit']);
    Route::post('purchases/{purchase}/restore', [PurchaseController::class, 'restore'])->name('purchases.restore');
    Route::resource('payments', PaymentController::class)->except(['show', 'create', 'edit']);
    Route::post('payments/{payment}/restore', [PaymentController::class, 'restore'])->name('payments.restore');

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/daily-purchases', [ReportController::class, 'dailyPurchases'])->name('daily-purchases');
        Route::get('/daily-purchases/export', [ReportController::class, 'exportDailyPurchases'])->name('daily-purchases.export');
        Route::get('/monthly-purchases/export', [ReportController::class, 'exportMonthlyPurchases'])->name('monthly-purchases.export');
        Route::get('/pump-ledger/export', [ReportController::class, 'exportPumpLedger'])->name('pump-ledger.export');
        Route::get('/outstanding/export', [ReportController::class, 'exportOutstanding'])->name('outstanding.export');
        Route::get('/vehicle-wise/export', [ReportController::class, 'exportVehicleWise'])->name('vehicle-wise.export');
        Route::get('/driver-wise/export', [ReportController::class, 'exportDriverWise'])->name('driver-wise.export');
        Route::get('/payments/export', [ReportController::class, 'exportPayments'])->name('payments.export');
        Route::get('/monthly-purchases', [ReportController::class, 'monthlyPurchases'])->name('monthly-purchases');
        Route::get('/pump-ledger', [ReportController::class, 'pumpLedger'])->name('pump-ledger');
        Route::get('/outstanding', [ReportController::class, 'outstanding'])->name('outstanding');
        Route::get('/vehicle-wise', [ReportController::class, 'vehicleWise'])->name('vehicle-wise');
        Route::get('/driver-wise', [ReportController::class, 'driverWise'])->name('driver-wise');
        Route::get('/payments', [ReportController::class, 'payments'])->name('payments');
    });

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('settings', [SettingsController::class, 'edit'])->name('settings');
        Route::patch('settings/company', [SettingsController::class, 'updateCompany'])->name('settings.company.update');

        Route::resource('pumps', PumpController::class)->except(['create', 'edit']);
        Route::post('pumps/{pump}/restore', [PumpController::class, 'restore'])->name('pumps.restore');
        Route::resource('vehicles', VehicleController::class)->except(['create', 'edit']);
        Route::post('vehicles/{vehicle}/restore', [VehicleController::class, 'restore'])->name('vehicles.restore');
        Route::resource('drivers', DriverController::class)->except(['create', 'edit']);
        Route::post('drivers/{driver}/restore', [DriverController::class, 'restore'])->name('drivers.restore');
        Route::resource('users', UserController::class)->except(['show', 'create', 'edit']);
        Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
    });
});

require __DIR__.'/auth.php';
