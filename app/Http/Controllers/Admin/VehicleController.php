<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VehicleRequest;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Support\OffcanvasDeepLinks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $vehicles = Vehicle::query()
            ->with('driver:id,name')
            ->orderBy('vehicle_number')
            ->get();

        $trashedVehicles = Vehicle::onlyTrashed()
            ->with('driver:id,name')
            ->orderBy('vehicle_number')
            ->get();

        $isEdit = old('_method') === 'patch';
        $editId = old('_edit_id');

        $offcanvasConfig = OffcanvasDeepLinks::apply([
            'openOnLoad' => $isEdit || session()->has('errors'),
            'initialMode' => $isEdit ? 'edit' : 'create',
            'initialTitle' => $isEdit ? 'Edit Vehicle' : 'Add Vehicle',
            'initialEditUrl' => $editId ? route('admin.vehicles.update', $editId) : '',
            'createTitle' => 'Add Vehicle',
            'editTitle' => 'Edit Vehicle',
            'storeUrl' => route('admin.vehicles.store'),
            'updateUrlTemplate' => route('admin.vehicles.update', ['vehicle' => '__ID__']),
            'defaults' => [
                'vehicle_number' => '',
                'registration_number' => '',
                'type' => 'CNG Auto Rickshaw',
                'driver_id' => '',
                'status' => \App\Enums\VehicleStatus::Active->value,
            ],
        ], $request, $vehicles, fn (Vehicle $vehicle) => [
            'id' => $vehicle->id,
            'vehicle_number' => $vehicle->vehicle_number,
            'registration_number' => $vehicle->registration_number,
            'type' => $vehicle->type,
            'driver_id' => (string) ($vehicle->driver_id ?? ''),
            'status' => $vehicle->status->value,
        ]);

        return view('admin.vehicles.index', [
            'vehicles' => $vehicles,
            'trashedVehicles' => $trashedVehicles,
            'drivers' => Driver::query()->orderBy('name')->get(['id', 'name']),
            'offcanvasConfig' => $offcanvasConfig,
        ]);
    }

    public function show(Vehicle $vehicle): View
    {
        $vehicle->load('driver');
        $vehicle->loadSum('purchases as purchases_total', 'amount');

        return view('admin.vehicles.show', [
            'vehicle' => $vehicle,
            'stats' => [
                'purchase_count' => $vehicle->purchases()->count(),
                'purchase_total' => (float) $vehicle->purchases_total,
                'quantity_total' => (float) $vehicle->purchases()->sum('quantity'),
            ],
            'recentPurchases' => $vehicle->purchases()
                ->with(['pump:id,name', 'driver:id,name'])
                ->latest('purchase_date')
                ->latest('id')
                ->limit(10)
                ->get(),
        ]);
    }

    public function store(VehicleRequest $request): RedirectResponse
    {
        Vehicle::query()->create($request->validated());

        return redirect()
            ->route('admin.vehicles.index')
            ->with('status', 'Vehicle added successfully.');
    }

    public function update(VehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update($request->validated());

        return redirect()
            ->route('admin.vehicles.show', $vehicle)
            ->with('status', 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();

        return redirect()
            ->route('admin.vehicles.index')
            ->with('status', 'Vehicle deleted successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        $vehicle = Vehicle::onlyTrashed()->findOrFail($id);
        $vehicle->restore();

        return redirect()
            ->route('admin.vehicles.index')
            ->with('status', 'Vehicle restored successfully.');
    }
}
