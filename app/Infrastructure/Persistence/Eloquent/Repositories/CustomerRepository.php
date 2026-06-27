<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Contracts\CustomerRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BaseRepository;
use App\Models\Customer;

class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    public function findByPhone(string $phone): ?Customer
    {
        /** @var Customer|null $customer */
        $customer = $this->model->newQuery()->where('phone', $phone)->first();

        return $customer;
    }
}
