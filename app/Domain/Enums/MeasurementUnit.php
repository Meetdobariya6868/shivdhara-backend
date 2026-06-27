<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum MeasurementUnit: string
{
    case Mm   = 'mm';
    case Inch = 'inch';
    case Feet = 'feet';

    public function label(): string
    {
        return match ($this) {
            self::Mm   => 'Millimeter',
            self::Inch => 'Inch',
            self::Feet => 'Feet',
        };
    }

    /** Multiply a value in this unit by the factor to get feet. */
    public function toFeetFactor(): float
    {
        return match ($this) {
            self::Mm   => 1 / 304.8,
            self::Inch => 1 / 12,
            self::Feet => 1.0,
        };
    }
}
