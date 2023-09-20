<?php

namespace Palax\LaravelHelpers\Helpers\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getById(int $id): ?Model
    {
        return $this->model::find($id);
    }
}
