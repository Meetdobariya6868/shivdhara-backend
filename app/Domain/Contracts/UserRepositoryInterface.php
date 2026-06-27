<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Models\User;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByMobile(string $mobile): ?User;
}
