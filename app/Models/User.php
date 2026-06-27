<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Admin or Salesman (Manager/Viewer reserved). Authenticates by phone number.
 *
 * @property UserRole $role
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'photo',
        'password',
        'role',
        'is_active',
        'can_create_orders',
        'login_mode',
        'fcm_token',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'can_create_orders' => 'boolean',
            'login_mode' => 'integer',
        ];
    }

    /**
     * Orders created by this user.
     *
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Whether this user may create orders. Admins always may; salesmen are
     * gated by both their active status and the admin-controlled permission.
     */
    public function canCreateOrders(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->is_active && $this->can_create_orders;
    }
}
