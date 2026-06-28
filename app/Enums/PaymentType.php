<?php

namespace App\Enums;

enum PaymentType: string
{
    case Payment = 'payment';
    case Advance = 'advance';

    public function label(): string
    {
        return match ($this) {
            self::Payment => 'Payment',
            self::Advance => 'Advance',
        };
    }
}
