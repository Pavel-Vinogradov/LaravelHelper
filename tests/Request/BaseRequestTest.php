<?php

declare(strict_types=1);

namespace Tizix\LaravelHelpers\Tests\Request;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Orchestra\Testbench\TestCase;
use Tizix\LaravelHelpers\Helpers\Request\BaseRequest;

final class TestRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ];
    }
}
final class BaseRequestTest extends TestCase
{
    public function testAuthorize(): void
    {
        $request = new TestRequest();

        $this->assertTrue($request->authorize());
    }

    public function testRules(): void
    {
        $request = new TestRequest();

        $this->assertEquals([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ], $request->rules());
    }

    /**
     * @throws BindingResolutionException
     */
    public function testFailedValidation(): void
    {
        $this->expectException(HttpResponseException::class);

        $request = new TestRequest();
        $request->setContainer($this->app)->validateResolved();

        $validator = app('validator')->make($request->all(), $request->rules());
        $request->failedValidation($validator);
    }
}
