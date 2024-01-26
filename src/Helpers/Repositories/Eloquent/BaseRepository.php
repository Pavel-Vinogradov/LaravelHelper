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


    public function create(array $attributes): ?Model
    {
        return $this->query()->create($attributes);
    }

    public function update(int $modelId, array $attributes): ?Model
    {
        $model = $this->findById($modelId);

        if (!$model) {
            return null;
        }
        $model->update($attributes);
        $model->save();
        return $model;
    }

    public function getAll(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->query()->with($relations)->get($columns);
    }

    public function deleteById(int $modelId): bool
    {
        $model = $this->findById($modelId);
        if (!$model) {
            return false;
        }
        return $model->delete();
    }

    private function query(): Builder
    {
        return $this->model->query();
    }

}
