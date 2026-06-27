<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'mobile',
    'address',
    'license_number',
])]
class Driver extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
