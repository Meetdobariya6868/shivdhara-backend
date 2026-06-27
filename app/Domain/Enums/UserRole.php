<?php

declare(strict_types=1);

namespace App\Domain\Enums;

/**
 * User roles. Values match the production data set; `Salesman` is stored as
 * "sales" for backward data compatibility. Admin and Salesman are the two
 * roles the application actively uses (Manager/Viewer reserved for future use).
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Salesman = 'sales';
    case Manager = 'manager';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Salesman => 'Salesman',
            self::Manager => 'Manager',
            self::Viewer => 'Viewer',
        };
    }
}
