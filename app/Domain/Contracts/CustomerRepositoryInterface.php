<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Models\Customer;

/**
 * Persistence contract for customers. The phone lookup powers de-duplication
 * (reuse an existing customer instead of creating a duplicate row).
 */
interface CustomerRepositoryInterface extends RepositoryInterface
{
    public function findByPhone(string $phone): ?Customer;
}
