<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Contracts\UserRepositoryInterface;
use App\Domain\Enums\UserRole;
use App\Infrastructure\Persistence\Eloquent\BaseRepository;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByMobile(string $mobile): ?User
    {
        /** @var User|null $user */
        $user = $this->model->newQuery()->where('mobile_number', $mobile)->first();

        return $user;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, User>
     */
    public function listSalesmen(array $filters): Collection
    {
        /** @var Collection<int, User> $result */
        $result = $this->model->newQuery()
            ->where('role', UserRole::Salesman->value)
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->get();

        return $result;
    }

    public function mobileExists(string $mobile, ?int $ignoreId = null): bool
    {
        return $this->model->newQuery()
            ->where('mobile_number', $mobile)
            ->when($ignoreId, fn ($q, $id) => $q->where('id', '!=', $id))
            ->exists();
    }
}
