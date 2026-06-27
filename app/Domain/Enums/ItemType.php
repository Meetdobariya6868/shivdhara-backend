<?php

declare(strict_types=1);

namespace App\Domain\Enums;

/**
 * How a line item's quantity is expressed, stored as a small integer
 * (matches the production `order_items.items_type` column).
 *
 * - Box   → quantity is a number of boxes (pieces = quantity × piece_per_box)
 * - Piece → quantity is a number of loose pieces
 */
enum ItemType: int
{
    case Box = 0;
    case Piece = 1;

    public function label(): string
    {
        return match ($this) {
            self::Box => 'Box',
            self::Piece => 'Piece',
        };
    }
}
