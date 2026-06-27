<?php

declare(strict_types=1);

namespace App\Application\Services\Auth;

use App\Application\DTOs\Auth\AuthResult;
use App\Application\DTOs\Auth\LoginDTO;
use App\Application\Services\BaseService;
use App\Domain\Contracts\UserRepositoryInterface;
use App\Domain\Enums\UserRole;
use App\Domain\Enums\UserStatus;
use App\Events\Auth\UserLoggedIn;
use App\Events\Auth\UserLoggedOut;
use App\Exceptions\Auth\AccountBlockedException;
use App\Exceptions\Auth\InvalidCredentialsException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class AuthService extends BaseService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Authenticate a user by mobile number and password.
     *
     * Security order:
     *   1. Find by mobile           → generic error if not found (prevents enumeration)
     *   2. Verify password hash     → generic error if wrong    (prevents enumeration)
     *   3. Check account status     → specific error if blocked (password already verified)
     *
     * @throws InvalidCredentialsException
     * @throws AccountBlockedException
     */
    public function login(LoginDTO $dto): AuthResult
    {
        $user = $this->userRepository->findByMobile($dto->mobileNumber);

        if ($user === null || ! Hash::check($dto->password, $user->password)) {
            throw new InvalidCredentialsException();
        }

        if ($user->status === UserStatus::Blocked) {
            throw new AccountBlockedException();
        }

        $token = $user->createToken(
            name: 'api_token',
            abilities: $this->resolveAbilities($user),
        );

        event(new UserLoggedIn($user));

        return new AuthResult(
            token: $token->plainTextToken,
            user: $user,
        );
    }

    /**
     * Revoke the current access token and fire a logout event.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
        event(new UserLoggedOut($user));
    }

    /**
     * Resolve Sanctum token abilities based on the user's role.
     * Admins receive a wildcard; salesmen receive a scoped subset.
     *
     * @return string[]
     */
    private function resolveAbilities(User $user): array
    {
        return match ($user->role) {
            UserRole::Admin    => ['*'],
            UserRole::Salesman => [
                'orders:read',
                'orders:create',
                'orders:update',
                'customers:read',
                'customers:create',
                'designs:read',
            ],
        };
    }
}
