<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DriverRequest extends FormRequest
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
            'mobile' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'license_number' => ['nullable', 'string', 'max:255'],
        ];
    }
}
