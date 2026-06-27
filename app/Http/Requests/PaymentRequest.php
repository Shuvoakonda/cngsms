<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Enums\PumpStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'payment_date' => ['required', 'date'],
            'pump_id' => ['required', Rule::exists('pumps', 'id')->where('status', PumpStatus::Active->value)],
            'voucher_number' => ['required', 'string', 'max:100'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'amount' => ['required', 'numeric', 'gt:0'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
