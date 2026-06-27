<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DriverRequest;
use App\Models\Driver;
use App\Support\OffcanvasDeepLinks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DriverController extends Controller
{
    public function index(Request $request): View
    {
        $drivers = Driver::query()
            ->withCount('vehicles')
            ->orderBy('name')
            ->get();

        $trashedDrivers = Driver::onlyTrashed()
            ->withCount('vehicles')
            ->orderBy('name')
            ->get();

        $isEdit = old('_method') === 'patch';
        $editId = old('_edit_id');

        $offcanvasConfig = OffcanvasDeepLinks::apply([
            'openOnLoad' => $isEdit || session()->has('errors'),
            'initialMode' => $isEdit ? 'edit' : 'create',
            'initialTitle' => $isEdit ? 'Edit Driver' : 'Add Driver',
            'initialEditUrl' => $editId ? route('admin.drivers.update', $editId) : '',
            'createTitle' => 'Add Driver',
            'editTitle' => 'Edit Driver',
            'storeUrl' => route('admin.drivers.store'),
            'updateUrlTemplate' => route('admin.drivers.update', ['driver' => '__ID__']),
            'defaults' => [
                'name' => '',
                'mobile' => '',
                'address' => '',
                'license_number' => '',
            ],
        ], $request, $drivers, fn (Driver $driver) => [
            'id' => $driver->id,
            'name' => $driver->name,
            'mobile' => $driver->mobile,
            'address' => $driver->address,
            'license_number' => $driver->license_number,
        ]);

        return view('admin.drivers.index', compact('drivers', 'trashedDrivers', 'offcanvasConfig'));
    }

    public function show(Driver $driver): View
    {
        $driver->loadCount('vehicles');
        $driver->loadSum('purchases as purchases_total', 'amount');

        return view('admin.drivers.show', [
            'driver' => $driver,
            'vehicles' => $driver->vehicles()->orderBy('vehicle_number')->get(),
            'stats' => [
                'purchase_count' => $driver->purchases()->count(),
                'purchase_total' => (float) $driver->purchases_total,
                'quantity_total' => (float) $driver->purchases()->sum('quantity'),
            ],
            'recentPurchases' => $driver->purchases()
                ->with(['pump:id,name', 'vehicle:id,vehicle_number'])
                ->latest('purchase_date')
                ->latest('id')
                ->limit(10)
                ->get(),
        ]);
    }

    public function store(DriverRequest $request): RedirectResponse
    {
        Driver::query()->create($request->validated());

        return redirect()
            ->route('admin.drivers.index')
            ->with('status', 'Driver added successfully.');
    }

    public function update(DriverRequest $request, Driver $driver): RedirectResponse
    {
        $driver->update($request->validated());

        return redirect()
            ->route('admin.drivers.show', $driver)
            ->with('status', 'Driver updated successfully.');
    }

    public function destroy(Driver $driver): RedirectResponse
    {
        $driver->delete();

        return redirect()
            ->route('admin.drivers.index')
            ->with('status', 'Driver deleted successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        $driver = Driver::onlyTrashed()->findOrFail($id);
        $driver->restore();

        return redirect()
            ->route('admin.drivers.index')
            ->with('status', 'Driver restored successfully.');
    }
}
