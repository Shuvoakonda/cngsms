<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name',
    'address',
    'logo_path',
    'currency',
    'date_format',
    'quantity_unit',
])]
class Company extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'name' => 'Marwha Enterprise',
            'currency' => 'BDT',
            'date_format' => 'd-m-Y',
            'quantity_unit' => 'M3',
        ]);
    }

    public function logoUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }
}
