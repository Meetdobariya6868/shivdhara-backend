<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Abstract Eloquent implementation of {@see RepositoryInterface}.
 *
 * Concrete repositories extend this class and inject the Eloquent model they
 * operate on. Centralising the CRUD plumbing here keeps concrete repositories
 * DRY and free of duplicated query code (the persistence layer is the only
 * place allowed to know about Eloquent).
 */
abstract class BaseRepository implements RepositoryInterface
{
    public function __construct(protected Model $model) {}

    public function all(): Collection
    {
        return $this->model->newQuery()->get();
    }

    public function find(int|string $id): ?Model
    {
        return $this->model->newQuery()->find($id);
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->model->newQuery()->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Model
    {
        return $this->model->newQuery()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(int|string $id, array $attributes): Model
    {
        $model = $this->findOrFail($id);
        $model->fill($attributes)->save();

        return $model;
    }

    public function delete(int|string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }
}
