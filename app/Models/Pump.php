<?php

namespace App\Models;

use App\Enums\PumpStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'address',
    'contact_person',
    'mobile',
    'opening_balance',
    'credit_limit',
    'status',
])]
class Pump extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'status' => PumpStatus::class,
        ];
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function outstanding(): float
    {
        $purchases = (float) $this->purchases()->sum('amount');
        $payments = (float) $this->payments()->sum('amount');

        return round((float) $this->opening_balance + $purchases - $payments, 2);
    }

    public function isOverCreditLimit(): bool
    {
        if ((float) $this->credit_limit <= 0) {
            return false;
        }

        return $this->outstanding() > (float) $this->credit_limit;
    }
}
