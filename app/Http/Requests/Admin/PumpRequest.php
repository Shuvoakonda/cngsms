<?php

namespace App\Http\Requests\Admin;

use App\Enums\PumpStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PumpRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'credit_limit' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(PumpStatus::class)],
        ];
    }
}
