<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Bank = 'bank';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Bank => 'Bank Transfer',
        };
    }
}
