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
    'opening_advance',
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
            'opening_advance' => 'decimal:2',
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

    public function outstandingAmount(?float $purchasesTotal = null, ?float $paymentsTotal = null): float
    {
        $purchases = $purchasesTotal ?? (float) $this->purchases()->sum('amount');
        $payments = $paymentsTotal ?? (float) $this->payments()->sum('amount');

        return round(
            (float) $this->opening_balance
            - (float) $this->opening_advance
            + $purchases
            - $payments,
            2,
        );
    }

    public function outstanding(): float
    {
        return $this->outstandingAmount();
    }

    public function dueAmount(?float $purchasesTotal = null, ?float $paymentsTotal = null): float
    {
        return max(0, $this->outstandingAmount($purchasesTotal, $paymentsTotal));
    }

    public function advanceBalance(?float $purchasesTotal = null, ?float $paymentsTotal = null): float
    {
        return max(0, -$this->outstandingAmount($purchasesTotal, $paymentsTotal));
    }

    public function isOverCreditLimit(): bool
    {
        if ((float) $this->credit_limit <= 0) {
            return false;
        }

        return $this->outstanding() > (float) $this->credit_limit;
    }
}
