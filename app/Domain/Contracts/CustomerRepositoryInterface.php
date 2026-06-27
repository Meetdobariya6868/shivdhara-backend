<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Models\Customer;

interface CustomerRepositoryInterface extends RepositoryInterface
{
    public function findByContact(string $contact): ?Customer;
}
