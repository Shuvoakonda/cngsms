<?php

namespace App\Http\Controllers;

use App\Enums\PumpStatus;
use App\Enums\VehicleStatus;
use App\Http\Requests\PurchaseRequest;
use App\Models\Driver;
use App\Models\Pump;
use App\Models\Purchase;
use App\Models\Vehicle;
use App\Support\OffcanvasDeepLinks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Purchase::query()
            ->with(['pump:id,name', 'vehicle:id,vehicle_number', 'driver:id,name'])
            ->latest('purchase_date')
            ->latest('id');

        if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->date_to);
        }

        if ($request->filled('pump_id')) {
            $query->where('pump_id', $request->pump_id);
        }

        if ($request->filled('vehicle_id')) {
            if ($request->vehicle_id === 'guest') {
                $query->whereNull('vehicle_id');
            } else {
                $query->where('vehicle_id', $request->vehicle_id);
            }
        }

        if ($request->input('status') === 'trashed') {
            $query->onlyTrashed();
        }

        $purchases = $query->paginate(20)->withQueryString();
        $isEdit = old('_method') === 'patch';
        $editId = old('_edit_id');

        $deepLinkRecords = collect($purchases->items());

        if ($request->filled('edit')) {
            $deepLinkPurchase = Purchase::withTrashed()->find((int) $request->query('edit'));

            if ($deepLinkPurchase && ! $deepLinkRecords->contains('id', $deepLinkPurchase->id)) {
                $deepLinkRecords->push($deepLinkPurchase);
            }
        }

        $offcanvasConfig = OffcanvasDeepLinks::apply([
            'openOnLoad' => $isEdit || session()->has('errors'),
            'initialMode' => $isEdit ? 'edit' : 'create',
            'initialTitle' => $isEdit ? 'Edit Purchase' : 'Add Purchase',
            'initialEditUrl' => $editId ? route('purchases.update', $editId) : '',
            'createTitle' => 'Add Purchase',
            'editTitle' => 'Edit Purchase',
            'storeUrl' => route('purchases.store'),
            'updateUrlTemplate' => route('purchases.update', ['purchase' => '__ID__']),
            'defaults' => [
                'purchase_date' => now()->toDateString(),
                'pump_id' => '',
                'slip_number' => '',
                'vehicle_id' => '',
                'driver_id' => '',
                'guest_reference' => '',
                'quantity' => '',
                'rate' => '',
                'remarks' => '',
            ],
        ], $request, $deepLinkRecords, fn (Purchase $purchase) => [
            'id' => $purchase->id,
            'purchase_date' => $purchase->purchase_date->toDateString(),
            'pump_id' => (string) $purchase->pump_id,
            'slip_number' => $purchase->slip_number,
            'vehicle_id' => (string) ($purchase->vehicle_id ?? ''),
            'driver_id' => (string) ($purchase->driver_id ?? ''),
            'guest_reference' => $purchase->guest_reference ?? '',
            'quantity' => (string) $purchase->quantity,
            'rate' => (string) $purchase->rate,
            'remarks' => $purchase->remarks,
        ]);

        return view('purchases.index', [
            'purchases' => $purchases,
            'pumps' => Pump::query()->where('status', PumpStatus::Active)->orderBy('name')->get(['id', 'name']),
            'vehicles' => Vehicle::query()->where('status', VehicleStatus::Active)->orderBy('vehicle_number')->get(['id', 'vehicle_number', 'driver_id']),
            'drivers' => Driver::query()->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['date_from', 'date_to', 'pump_id', 'vehicle_id', 'status']),
            'offcanvasConfig' => $offcanvasConfig,
        ]);
    }

    public function store(PurchaseRequest $request): RedirectResponse
    {
        Purchase::query()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('purchases.index')
            ->with('status', 'Purchase entry saved successfully.');
    }

    public function update(PurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        $purchase->update($request->validated());

        return redirect()
            ->route('purchases.index')
            ->with('status', 'Purchase entry updated successfully.');
    }

    public function destroy(Purchase $purchase): RedirectResponse
    {
        abort_unless(auth()->user()?->canDeleteRecords(), 403);

        $purchase->delete();

        return redirect()
            ->route('purchases.index')
            ->with('status', 'Purchase entry deleted successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        abort_unless(auth()->user()?->canDeleteRecords(), 403);

        $purchase = Purchase::onlyTrashed()->findOrFail($id);
        $purchase->restore();

        return redirect()
            ->route('purchases.index')
            ->with('status', 'Purchase entry restored successfully.');
    }
}
