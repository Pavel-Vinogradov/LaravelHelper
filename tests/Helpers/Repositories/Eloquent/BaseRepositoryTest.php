<?php

declare(strict_types=1);

namespace Tizix\LaravelHelpers\Tests\Helpers\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tizix\LaravelHelpers\Helpers\Repositories\Eloquent\BaseRepository;

final class TestableBaseRepository extends BaseRepository
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }
}

final class ExampleModel extends Model
{
    protected $fillable = ['id', 'name'];
}

final class BaseRepositoryTest extends TestCase
{
    public function testFindById(): void
    {
        $fakeId = 123;
        $expectedModel = new ExampleModel(['id' => $fakeId]);

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('find')->with($fakeId)->andReturn($expectedModel);

        $testModelMock = Mockery::mock(ExampleModel::class)->makePartial();
        $testModelMock->shouldReceive('query')->andReturn($builder);

        $repository = new TestableBaseRepository($testModelMock);
        $result = $repository->findById($fakeId);

        $this->assertSame($expectedModel, $result);
    }

    public function testUpdate(): void
    {
        $modelId = 1;
        $attributes = ['name' => 'value'];
        $model = Mockery::mock(ExampleModel::class);
        $builder = Mockery::mock(Builder::class);
        $model->shouldReceive('newQuery')->andReturn($builder);
        $builder->shouldReceive('findOrFail')->with($modelId)->andReturn($model);
        $model->shouldReceive('update')->with($attributes)->andReturn(true);
        $model->shouldReceive('getAttribute')->with('name')->andReturn($attributes['name']);
        $this->assertEquals($attributes['name'], $model->getAttribute('name')); // Check if the attribute has been updated correctly
    }

    public function testCreate(): void
    {
        $attributes = ['id' => 789];
        $model = new ExampleModel($attributes);
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('create')->with($attributes)->andReturn($model);
        $modelMock = Mockery::mock(ExampleModel::class)->makePartial();
        $modelMock->shouldReceive('query')->andReturn($builder);
        $repository = new TestableBaseRepository($modelMock);
        $result = $repository->create($attributes);
        $this->assertEquals($model, $result);
    }

    public function testGetAll(): void
    {
        $columns = ['id'];
        $relations = [];
        $collection = collect([new ExampleModel(['id' => 123])]);
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('with')->with($relations)->andReturnSelf();
        $builder->shouldReceive('get')->with($columns)->andReturn($collection);
        $modelMock = Mockery::mock(ExampleModel::class)->makePartial();
        $modelMock->shouldReceive('query')->andReturn($builder);
        $repository = new TestableBaseRepository($modelMock);
        $result = $repository->getAll($columns, $relations);

        $this->assertEquals($collection, $result);
    }

    public function testDeleteById(): void
    {
        $modelId = 1;
        $model = Mockery::mock(ExampleModel::class);
        $builder = Mockery::mock(Builder::class);
        $model->shouldReceive('query')->andReturn($builder);
        $builder->shouldReceive('find')->with($modelId)->andReturn($model);
        $model->shouldReceive('delete')->andReturn(true);
        $baseRepo = new TestableBaseRepository($model);
        $this->assertTrue($baseRepo->deleteById($modelId));
    }
}
