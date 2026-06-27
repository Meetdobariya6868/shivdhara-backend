<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Enums\AppPlatform;
use Database\Factories\AppVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property AppPlatform $platform
 */
class AppVersion extends Model
{
    /** @use HasFactory<AppVersionFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['version', 'platform', 'is_latest', 'force_update', 'release_notes'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'platform' => AppPlatform::class,
            'is_latest' => 'boolean',
            'force_update' => 'boolean',
        ];
    }
}
