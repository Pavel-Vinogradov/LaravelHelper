<?php

declare(strict_types=1);

namespace Tizix\LaravelHelpers\Helpers\Repositories\Eloquent\Interface;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface EloquentRepositoryInterface
{
    public function findById(int $modelId): ?Model;

    public function create(array $attributes): ?Model;

    public function update(int $modelId, array $attributes): ?Model;

    public function deleteById(int $modelId): bool;

    public function getAll(array $columns = ['*'], array $relations = []): Collection;
}
