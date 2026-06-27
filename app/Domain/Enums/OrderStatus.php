<?php

declare(strict_types=1);

namespace App\Domain\Enums;

/**
 * Order lifecycle, stored as a small integer (matches the production
 * `orders.order_status` column). Transitions are enforced in the service layer
 * and recorded in `order_status_history`.
 */
enum OrderStatus: int
{
    case Draft = 0;
    case Confirmed = 1;
    case Processing = 2;
    case Dispatched = 3;
    case Delivered = 4;
    case Cancelled = 5;

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Confirmed => 'Confirmed',
            self::Processing => 'Processing',
            self::Dispatched => 'Dispatched',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }
}
