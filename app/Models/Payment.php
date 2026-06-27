<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'payment_date',
    'pump_id',
    'voucher_number',
    'payment_method',
    'amount',
    'reference_number',
    'remarks',
    'created_by',
])]
class Payment extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
        ];
    }

    public function pump(): BelongsTo
    {
        return $this->belongsTo(Pump::class)->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
