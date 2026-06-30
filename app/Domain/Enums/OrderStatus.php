<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum OrderStatus: string
{
    case Pending   = 'pending';
    case Confirmed = 'confirmed';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Pending',
            self::Confirmed => 'Confirmed',
        };
    }

    public function isPending(): bool
    {
        return $this === self::Pending;
    }
}
