<?php

declare(strict_types=1);

namespace Tizix\LaravelHelpers\Helpers\Repositories\Eloquent\Interface;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface EloquentRepositoryInterface
{
    public function findById(int $modelId): ?Model;

    public function create(array $attributes): Model;

    public function update(Model $model, array $attributes): Model;

    public function delete(Model $model): bool;

    public function getAll(array $columns = ['*'], array $relations = []): Collection;
}
