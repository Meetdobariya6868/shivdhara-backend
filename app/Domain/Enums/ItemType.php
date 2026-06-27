<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum ItemType: string
{
    case Box   = 'box';
    case Piece = 'piece';

    public function label(): string
    {
        return match ($this) {
            self::Box   => 'Box',
            self::Piece => 'Piece',
        };
    }
}
