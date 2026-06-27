<?php

namespace App\Models;

use App\Enums\VehicleStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'vehicle_number',
    'registration_number',
    'type',
    'driver_id',
    'status',
])]
class Vehicle extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => VehicleStatus::class,
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class)->withTrashed();
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
