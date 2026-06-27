<?php

namespace App\Http\Requests;

use App\Enums\PumpStatus;
use App\Enums\VehicleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'vehicle_id' => $this->input('vehicle_id') ?: null,
            'driver_id' => $this->input('driver_id') ?: null,
            'guest_reference' => $this->filled('guest_reference') ? trim((string) $this->input('guest_reference')) : null,
        ]);

        if ($this->filled(['quantity', 'rate'])) {
            $this->merge([
                'amount' => round((float) $this->input('quantity') * (float) $this->input('rate'), 2),
            ]);
        }

        if ($this->input('vehicle_id')) {
            $this->merge(['guest_reference' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $purchaseId = $this->route('purchase')?->id;

        return [
            'purchase_date' => ['required', 'date'],
            'vehicle_id' => ['nullable', Rule::exists('vehicles', 'id')->where('status', VehicleStatus::Active->value)],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'guest_reference' => ['nullable', 'string', 'max:100'],
            'pump_id' => ['required', Rule::exists('pumps', 'id')->where('status', PumpStatus::Active->value)],
            'slip_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('purchases', 'slip_number')
                    ->where('pump_id', $this->input('pump_id'))
                    ->ignore($purchaseId),
            ],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'rate' => ['required', 'numeric', 'gt:0'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slip_number.unique' => 'This slip number already exists for the selected pump.',
        ];
    }
}
