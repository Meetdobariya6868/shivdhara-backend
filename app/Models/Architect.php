<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ArchitectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Architect extends Model
{
    /** @use HasFactory<ArchitectFactory> */
    use HasFactory, SoftDeletes;

    /** @var list<string> */
    protected $fillable = ['name', 'phone', 'email', 'firm_name', 'city', 'is_active'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
