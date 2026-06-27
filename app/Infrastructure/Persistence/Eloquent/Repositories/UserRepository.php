<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Contracts\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BaseRepository;
use App\Models\User;

/**
 * @extends BaseRepository
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByPhone(string $phone): ?User
    {
        /** @var User|null $user */
        $user = $this->model->newQuery()->where('phone', $phone)->first();

        return $user;
    }
}
