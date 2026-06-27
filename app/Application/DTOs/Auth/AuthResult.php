<?php

declare(strict_types=1);

namespace App\Application\DTOs\Auth;

use App\Models\User;

/**
 * Value Object returned by AuthService::login().
 * Carries the plain-text Sanctum token and the authenticated User model
 * across the service boundary to the HTTP layer without coupling either
 * layer to Sanctum's internal NewAccessToken structure.
 */
final readonly class AuthResult
{
    public function __construct(
        public string $token,
        public User   $user,
    ) {}
}
