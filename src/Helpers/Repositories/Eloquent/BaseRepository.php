<?php

declare(strict_types=1);

namespace Tizix\LaravelHelpers\Helpers\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Tizix\LaravelHelpers\Helpers\Repositories\Eloquent\Interface\EloquentRepositoryInterface;

abstract class BaseRepository implements EloquentRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function findById(int $modelId): ?Model
    {
        return $this->query()->find($modelId);
    }

    public function create(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }

    public function update(Model $model, array $attributes): Model
    {
        $model->update($attributes);
        return $model;
    }

    public function getAll(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->query()->with($relations)->get($columns);
    }

    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    private function query(): Builder
    {
        return $this->model->query();
    }
}
