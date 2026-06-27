<?php

declare(strict_types=1);

namespace App\Domain\Enums;

/**
 * Target platform for a published mobile app version.
 */
enum AppPlatform: string
{
    case Android = 'android';
    case Ios = 'ios';
    case Both = 'both';

    public function label(): string
    {
        return match ($this) {
            self::Android => 'Android',
            self::Ios => 'iOS',
            self::Both => 'Both',
        };
    }
}
