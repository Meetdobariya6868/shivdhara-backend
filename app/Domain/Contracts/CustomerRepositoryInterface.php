<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

/**
 * Persistence contract for customers. Customers are never deduped or shared
 * across orders — every order gets its own dedicated record (see
 * OrderService::createOrder) — so no lookup-by-contact method is needed beyond
 * the generic CRUD operations on {@see RepositoryInterface}.
 */
interface CustomerRepositoryInterface extends RepositoryInterface
{
}
