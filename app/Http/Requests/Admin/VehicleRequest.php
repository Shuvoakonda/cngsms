<?php

namespace App\Http\Requests\Admin;

use App\Enums\VehicleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageSettings() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $vehicleId = $this->route('vehicle')?->id;

        return [
            'vehicle_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('vehicles', 'vehicle_number')->ignore($vehicleId),
            ],
            'registration_number' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'max:100'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'status' => ['required', Rule::enum(VehicleStatus::class)],
        ];
    }
}
