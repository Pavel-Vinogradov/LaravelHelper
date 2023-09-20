<?php

namespace Palax\LaravelHelpers\Tests\Repositories;

use Illuminate\Database\Eloquent\Model;
use Mockery;
use Orchestra\Testbench\TestCase;
use Palax\LaravelHelpers\Helpers\Repositories\BaseRepository;

class TestableBaseRepository extends BaseRepository
{
}

class ExampleModel extends Model
{
    protected $fillable = ['id'];
}

class BaseRepositoryTest extends TestCase
{
    public function testGetById()
    {
        $fakeId = 123;
        $expectedModel = new ExampleModel(['id' => $fakeId]);
        $testModelMock = Mockery::mock(ExampleModel::class)->makePartial();
        $testModelMock->shouldReceive('find')->with($fakeId)->andReturn($expectedModel);
        $repository = new TestableBaseRepository($testModelMock);
        $result = $repository->getById($fakeId);

        $this->assertSame($expectedModel, $result);
    }
}
