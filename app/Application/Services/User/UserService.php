<?php

declare(strict_types=1);

namespace App\Application\Services\User;

use App\Application\DTOs\User\CreateUserDTO;
use App\Application\DTOs\User\UpdateUserDTO;
use App\Application\Services\BaseService;
use App\Domain\Contracts\OrderRepositoryInterface;
use App\Domain\Contracts\UserRepositoryInterface;
use App\Domain\Enums\UserRole;
use App\Domain\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * All business logic for the User Management module.
 * Controllers call these methods; this layer never touches HTTP concerns.
 */
final class UserService extends BaseService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, User>
     */
    public function listSalesmen(array $filters): Collection
    {
        return $this->userRepository->listSalesmen($filters);
    }

    /**
     * Create a salesman account. Role is forced to salesman and the password
     * is hashed by the model cast. Wrapped in a transaction for atomicity.
     */
    public function createSalesman(CreateUserDTO $dto, User $creator): User
    {
        return DB::transaction(function () use ($dto, $creator): User {
            $user = $this->userRepository->create([
                ...$dto->toArray(),
                'password'      => $dto->password,
                'role'          => UserRole::Salesman->value,
                'status'        => UserStatus::Active->value,
                'created_by_id' => $creator->id,
            ]);

            Log::info('[Users] Salesman created', [
                'created_user_id' => $user->id,
                'by_admin_id'     => $creator->id,
            ]);

            return $user;
        });
    }

    public function updateSalesman(User $user, UpdateUserDTO $dto): User
    {
        return DB::transaction(
            fn (): User => $this->userRepository->update($user->id, $dto->toArray()),
        );
    }

    /**
     * Grant or revoke a salesman's permission to create orders. No token
     * revocation is needed — OrderPolicy@create reads the live flag on the
     * salesman's next request, so a revoked permission takes effect immediately.
     */
    public function updatePermissions(User $user, bool $canCreateOrders): User
    {
        return DB::transaction(function () use ($user, $canCreateOrders): User {
            $updated = $this->userRepository->update($user->id, [
                'can_create_orders' => $canCreateOrders,
            ]);

            Log::info('[Users] Permissions changed', [
                'user_id'           => $user->id,
                'can_create_orders' => $canCreateOrders,
            ]);

            return $updated;
        });
    }

    /**
     * Block or unblock a salesman. When blocking, all of the user's active
     * API tokens are revoked so the change takes effect immediately.
     */
    public function changeStatus(User $user, UserStatus $status): User
    {
        return DB::transaction(function () use ($user, $status): User {
            $updated = $this->userRepository->update($user->id, ['status' => $status->value]);

            if ($status === UserStatus::Blocked) {
                $updated->tokens()->delete();
            }

            Log::info('[Users] Status changed', [
                'user_id' => $user->id,
                'status'  => $status->value,
            ]);

            return $updated;
        });
    }

    public function resetPassword(User $user, string $password): User
    {
        return DB::transaction(function () use ($user, $password): User {
            // The User model's `password` => 'hashed' cast hashes on set.
            $updated = $this->userRepository->update($user->id, [
                'password' => $password,
            ]);

            // Force re-login everywhere after a password reset.
            $updated->tokens()->delete();

            Log::info('[Users] Password reset', ['user_id' => $user->id]);

            return $updated;
        });
    }

    /**
     * Soft-delete a salesman: their orders are soft-deleted first (so nothing
     * references a removed account and they drop out of every list and filter),
     * then their tokens are revoked and the account itself is soft-deleted.
     * The whole cascade runs in one transaction for atomicity.
     */
    public function deleteSalesman(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $deletedOrders = $this->orderRepository->deleteByCreator($user->id);

            $user->tokens()->delete();
            $this->userRepository->delete($user->id);

            Log::info('[Users] Salesman deleted', [
                'user_id'        => $user->id,
                'deleted_orders' => $deletedOrders,
            ]);
        });
    }
}
