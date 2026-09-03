<?php

namespace App\Enums;

enum PurchaseStatus: string
{
    case Unsold = 'unsold';
    case Sold = 'sold';

    public function label(): string
    {
        return match ($this) {
            self::Unsold => 'Unsold',
            self::Sold => 'Sold',
        };
    }
}
