<?php

namespace App\Http\Controllers;

use App\Enums\PumpStatus;
use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Models\Pump;
use App\Support\OffcanvasDeepLinks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payment::query()
            ->with('pump:id,name')
            ->latest('payment_date')
            ->latest('id');

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        if ($request->filled('pump_id')) {
            $query->where('pump_id', $request->pump_id);
        }

        if ($request->input('status') === 'trashed') {
            $query->onlyTrashed();
        }

        $payments = $query->paginate(20)->withQueryString();
        $isEdit = old('_method') === 'patch';
        $editId = old('_edit_id');

        $deepLinkRecords = collect($payments->items());

        if ($request->filled('edit')) {
            $deepLinkPayment = Payment::withTrashed()->find((int) $request->query('edit'));

            if ($deepLinkPayment && ! $deepLinkRecords->contains('id', $deepLinkPayment->id)) {
                $deepLinkRecords->push($deepLinkPayment);
            }
        }

        $offcanvasConfig = OffcanvasDeepLinks::apply([
            'openOnLoad' => $isEdit || session()->has('errors'),
            'initialMode' => $isEdit ? 'edit' : 'create',
            'initialTitle' => $isEdit ? 'Edit Payment' : 'Add Payment',
            'initialEditUrl' => $editId ? route('payments.update', $editId) : '',
            'createTitle' => 'Add Payment',
            'editTitle' => 'Edit Payment',
            'storeUrl' => route('payments.store'),
            'updateUrlTemplate' => route('payments.update', ['payment' => '__ID__']),
            'defaults' => [
                'payment_date' => now()->toDateString(),
                'pump_id' => '',
                'type' => \App\Enums\PaymentType::Payment->value,
                'voucher_number' => '',
                'payment_method' => \App\Enums\PaymentMethod::Cash->value,
                'amount' => '',
                'reference_number' => '',
                'remarks' => '',
            ],
        ], $request, $deepLinkRecords, fn (Payment $payment) => [
            'id' => $payment->id,
            'payment_date' => $payment->payment_date->toDateString(),
            'pump_id' => (string) $payment->pump_id,
            'type' => $payment->type->value,
            'voucher_number' => $payment->voucher_number,
            'payment_method' => $payment->payment_method->value,
            'amount' => (string) $payment->amount,
            'reference_number' => $payment->reference_number,
            'remarks' => $payment->remarks,
        ]);

        return view('payments.index', [
            'payments' => $payments,
            'pumps' => Pump::query()->where('status', PumpStatus::Active)->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['date_from', 'date_to', 'pump_id', 'status']),
            'offcanvasConfig' => $offcanvasConfig,
        ]);
    }

    public function store(PaymentRequest $request): RedirectResponse
    {
        Payment::query()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('payments.index')
            ->with('status', 'Payment recorded successfully.');
    }

    public function update(PaymentRequest $request, Payment $payment): RedirectResponse
    {
        $payment->update($request->validated());

        return redirect()
            ->route('payments.index')
            ->with('status', 'Payment updated successfully.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        abort_unless(auth()->user()?->canDeleteRecords(), 403);

        $payment->delete();

        return redirect()
            ->route('payments.index')
            ->with('status', 'Payment deleted successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        abort_unless(auth()->user()?->canDeleteRecords(), 403);

        $payment = Payment::onlyTrashed()->findOrFail($id);
        $payment->restore();

        return redirect()
            ->route('payments.index')
            ->with('status', 'Payment restored successfully.');
    }
}
