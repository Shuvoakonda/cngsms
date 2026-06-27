<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PumpRequest;
use App\Models\Pump;
use App\Support\OffcanvasDeepLinks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PumpController extends Controller
{
    public function index(Request $request): View
    {
        $pumps = Pump::query()
            ->withSum('purchases as purchases_total', 'amount')
            ->withSum('payments as payments_total', 'amount')
            ->orderBy('name')
            ->get();

        $trashedPumps = Pump::onlyTrashed()
            ->withSum('purchases as purchases_total', 'amount')
            ->withSum('payments as payments_total', 'amount')
            ->orderBy('name')
            ->get();

        $isEdit = old('_method') === 'patch';
        $editId = old('_edit_id');

        $offcanvasConfig = OffcanvasDeepLinks::apply([
            'openOnLoad' => $isEdit || session()->has('errors'),
            'initialMode' => $isEdit ? 'edit' : 'create',
            'initialTitle' => $isEdit ? 'Edit Pump' : 'Add Pump',
            'initialEditUrl' => $editId ? route('admin.pumps.update', $editId) : '',
            'createTitle' => 'Add Pump',
            'editTitle' => 'Edit Pump',
            'storeUrl' => route('admin.pumps.store'),
            'updateUrlTemplate' => route('admin.pumps.update', ['pump' => '__ID__']),
            'defaults' => [
                'name' => '',
                'contact_person' => '',
                'mobile' => '',
                'opening_balance' => '0',
                'credit_limit' => '0',
                'status' => \App\Enums\PumpStatus::Active->value,
                'address' => '',
            ],
        ], $request, $pumps, fn (Pump $pump) => [
            'id' => $pump->id,
            'name' => $pump->name,
            'contact_person' => $pump->contact_person,
            'mobile' => $pump->mobile,
            'opening_balance' => (string) $pump->opening_balance,
            'credit_limit' => (string) $pump->credit_limit,
            'status' => $pump->status->value,
            'address' => $pump->address,
        ]);

        return view('admin.pumps.index', compact('pumps', 'trashedPumps', 'offcanvasConfig'));
    }

    public function show(Pump $pump): View
    {
        $pump->loadSum('purchases as purchases_total', 'amount')
            ->loadSum('payments as payments_total', 'amount');

        $outstanding = round(
            (float) $pump->opening_balance + (float) $pump->purchases_total - (float) $pump->payments_total,
            2,
        );

        return view('admin.pumps.show', [
            'pump' => $pump,
            'outstanding' => $outstanding,
            'stats' => [
                'purchase_count' => $pump->purchases()->count(),
                'payment_count' => $pump->payments()->count(),
                'purchase_total' => (float) $pump->purchases_total,
                'payment_total' => (float) $pump->payments_total,
            ],
            'recentPurchases' => $pump->purchases()
                ->with(['vehicle:id,vehicle_number', 'driver:id,name'])
                ->latest('purchase_date')
                ->latest('id')
                ->limit(10)
                ->get(),
            'recentPayments' => $pump->payments()
                ->latest('payment_date')
                ->latest('id')
                ->limit(10)
                ->get(),
        ]);
    }

    public function store(PumpRequest $request): RedirectResponse
    {
        Pump::query()->create($request->validated());

        return redirect()
            ->route('admin.pumps.index')
            ->with('status', 'Pump added successfully.');
    }

    public function update(PumpRequest $request, Pump $pump): RedirectResponse
    {
        $pump->update($request->validated());

        return redirect()
            ->route('admin.pumps.show', $pump)
            ->with('status', 'Pump updated successfully.');
    }

    public function destroy(Pump $pump): RedirectResponse
    {
        $pump->delete();

        return redirect()
            ->route('admin.pumps.index')
            ->with('status', 'Pump deleted successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        $pump = Pump::onlyTrashed()->findOrFail($id);
        $pump->restore();

        return redirect()
            ->route('admin.pumps.index')
            ->with('status', 'Pump restored successfully.');
    }
}
