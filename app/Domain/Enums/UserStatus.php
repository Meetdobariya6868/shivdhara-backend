<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum UserStatus: string
{
    case Active  = 'active';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Active  => 'Active',
            self::Blocked => 'Blocked',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }
}
