<?php

declare(strict_types=1);

namespace App\Domain\Enums;

/**
 * Unit in which an item's height/width are captured. The factor converts a
 * value in this unit into feet, the canonical unit for area (sq ft).
 */
enum MeasurementUnit: string
{
    case Millimeter = 'mm';
    case Centimeter = 'cm';
    case Inch = 'inch';
    case Feet = 'feet';

    /**
     * Multiplier to convert a measurement in this unit to feet.
     * 1 ft = 304.8 mm = 30.48 cm = 12 in.
     */
    public function toFeetFactor(): float
    {
        return match ($this) {
            self::Millimeter => 1 / 304.8,
            self::Centimeter => 1 / 30.48,
            self::Inch => 1 / 12,
            self::Feet => 1.0,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Millimeter => 'Millimeter (mm)',
            self::Centimeter => 'Centimeter (cm)',
            self::Inch => 'Inch',
            self::Feet => 'Feet',
        };
    }
}
