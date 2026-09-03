<?php

namespace App\Http\Requests;

use App\Enums\FuelType;
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
            'fuel_type' => $this->filled('fuel_type') ? $this->input('fuel_type') : FuelType::CNG->value,
            'discount_value' => $this->filled('discount_value') ? $this->input('discount_value') : null,
            'discount_type' => $this->filled('discount_value') ? ($this->input('discount_type') ?: 'taka') : null,
            'bonus_value' => $this->filled('bonus_value') ? $this->input('bonus_value') : null,
            'bonus_type' => $this->filled('bonus_value') ? ($this->input('bonus_type') ?: 'taka') : null,
        ]);

        if ($this->filled(['quantity', 'rate'])) {
            $baseAmount = round((float) $this->input('quantity') * (float) $this->input('rate'), 2);
            $discount = $this->adjustmentAmount($baseAmount, $this->input('discount_value'), $this->input('discount_type'));
            $bonus = $this->adjustmentAmount($baseAmount, $this->input('bonus_value'), $this->input('bonus_type'));

            $this->merge([
                'amount' => round($baseAmount - $discount + $bonus, 2),
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
            'fuel_type' => ['required', Rule::in(array_map(fn (FuelType $type) => $type->value, FuelType::cases()))],
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
            'discount_value' => ['nullable', 'numeric', 'gte:0'],
            'discount_type' => ['nullable', 'required_with:discount_value', Rule::in(['taka', 'percent'])],
            'bonus_value' => ['nullable', 'numeric', 'gte:0'],
            'bonus_type' => ['nullable', 'required_with:bonus_value', Rule::in(['taka', 'percent'])],
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

    private function adjustmentAmount(float $baseAmount, mixed $value, mixed $type): float
    {
        $value = (float) ($value ?: 0);

        if ($value <= 0) {
            return 0;
        }

        return $type === 'percent'
            ? round($baseAmount * ($value / 100), 2)
            : round($value, 2);
    }
}
