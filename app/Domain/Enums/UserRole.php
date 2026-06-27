<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum UserRole: string
{
    case Admin    = 'admin';
    case Salesman = 'salesman';

    public function label(): string
    {
        return match ($this) {
            self::Admin    => 'Admin',
            self::Salesman => 'Salesman',
        };
    }

    public function canCreateOrders(): bool
    {
        return true;
    }
}
