<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Enums\UserRole;
use App\Domain\Enums\UserStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'mobile_number',
        'password',
        'role',
        'status',
        'can_create_orders',
        'created_by_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'role'              => UserRole::class,
            'status'            => UserStatus::class,
            'can_create_orders' => 'boolean',
            'password'          => 'hashed',
        ];
    }

    /** Orders this user created. */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'creator_id');
    }

    /** Orders this user last updated. */
    public function updatedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'updated_by_id');
    }

    /** Customers this user registered. */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'created_by_id');
    }

    /** Users provisioned by this admin. */
    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by_id');
    }

    /** The admin who provisioned this user. */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function canCreateOrders(): bool
    {
        return $this->isActive() && $this->can_create_orders;
    }
}
