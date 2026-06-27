<?php

namespace App\Enums;

enum UserRole: string
{
    case Administrator = 'administrator';
    case DataEntry = 'data_entry';

    public function label(): string
    {
        return match ($this) {
            self::Administrator => 'Administrator',
            self::DataEntry => 'Data Entry User',
        };
    }
}
