<?php

namespace Palax\LaravelHelpers\Tests\Request;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Orchestra\Testbench\TestCase;
use Palax\LaravelHelpers\Helpers\Request\BaseRequest;

class TestRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ];
    }
}
class BaseRequestTest extends TestCase
{
    public function testAuthorize()
    {
        $request = new TestRequest();

        $this->assertTrue($request->authorize());
    }

    public function testRules()
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
