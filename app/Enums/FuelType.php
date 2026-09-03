<?php

namespace App\Enums;

enum FuelType: string
{
    case Diesel = 'diesel';
    case CNG = 'cng';

    public function label(): string
    {
        return match ($this) {
            self::Diesel => 'Diesel',
            self::CNG => 'CNG',
        };
    }
}
