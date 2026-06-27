<?php

declare(strict_types=1);

namespace App\Domain\Enums;

/**
 * Channel an order comes through. An `Architect` order is linked to an
 * `architects` row; a `Local` (walk-in) order is not.
 */
enum OrderType: string
{
    case Local = 'Local';
    case Architect = 'Architect';

    public function label(): string
    {
        return $this->value;
    }
}
