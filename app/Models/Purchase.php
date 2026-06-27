<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'purchase_date',
    'vehicle_id',
    'driver_id',
    'guest_reference',
    'pump_id',
    'slip_number',
    'quantity',
    'rate',
    'amount',
    'remarks',
    'created_by',
])]
class Purchase extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'quantity' => 'decimal:2',
            'rate' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class)->withTrashed();
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class)->withTrashed();
    }

    public function pump(): BelongsTo
    {
        return $this->belongsTo(Pump::class)->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function displayVehicle(): string
    {
        if ($this->vehicle) {
            return $this->vehicle->vehicle_number;
        }

        return $this->guest_reference
            ? 'Guest — '.$this->guest_reference
            : 'Guest';
    }

    public function displayDriver(): string
    {
        return $this->driver?->name ?? 'Guest';
    }

    public function isGuestPurchase(): bool
    {
        return $this->vehicle_id === null;
    }
}
