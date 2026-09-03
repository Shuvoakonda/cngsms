<?php

namespace App\Models;

use App\Enums\FuelType;
use App\Enums\PurchaseStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'purchase_date',
    'vehicle_id',
    'driver_id',
    'guest_reference',
    'pump_id',
    'fuel_type',
    'status',
    'slip_number',
    'quantity',
    'rate',
    'discount_value',
    'discount_type',
    'bonus_value',
    'bonus_type',
    'amount',
    'remarks',
    'created_by',
])]
class Purchase extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $attributes = [
        'status' => 'unsold',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'fuel_type' => FuelType::class,
            'status' => PurchaseStatus::class,
            'quantity' => 'decimal:2',
            'rate' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'bonus_value' => 'decimal:2',
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

    public function discountAmount(): float
    {
        return $this->adjustmentAmount($this->discount_value, $this->discount_type);
    }

    public function bonusAmount(): float
    {
        return $this->adjustmentAmount($this->bonus_value, $this->bonus_type);
    }

    public function displayDiscount(): string
    {
        return $this->displayAdjustment($this->discount_value, $this->discount_type, $this->discountAmount());
    }

    public function displayBonus(): string
    {
        return $this->displayAdjustment($this->bonus_value, $this->bonus_type, $this->bonusAmount());
    }

    private function adjustmentAmount(mixed $value, ?string $type): float
    {
        $value = (float) ($value ?: 0);

        if ($value <= 0) {
            return 0.0;
        }

        $baseAmount = (float) $this->quantity * (float) $this->rate;

        return $type === 'percent'
            ? round($baseAmount * ($value / 100), 2)
            : round($value, 2);
    }

    private function displayAdjustment(mixed $value, ?string $type, float $amount): string
    {
        $value = (float) ($value ?: 0);

        if ($value <= 0) {
            return '—';
        }

        if ($type === 'percent') {
            return number_format($value, 2).'% ('.number_format($amount, 2).')';
        }

        return number_format($amount, 2);
    }
}
