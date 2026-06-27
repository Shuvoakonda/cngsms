<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
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
            'currency' => ['required', 'string', 'max:10'],
            'date_format' => ['required', 'string', 'max:20'],
            'quantity_unit' => ['required', 'string', 'max:10'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }
}
