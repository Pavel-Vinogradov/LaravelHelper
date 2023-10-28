<?php

declare(strict_types=1);

namespace Tizix\LaravelHelpers\Tests\Helpers\Response;

use Illuminate\Http\JsonResponse;
use Orchestra\Testbench\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Response;
use Tizix\LaravelHelpers\Helpers\Response\ResponseHelper;

final class ResponseHelperTest extends TestCase
{
    public function testSuccessResponse(): void
    {
        $data = ['message' => 'Success'];
        $response = ResponseHelper::success($data);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(json_encode(['status' => true, 'errors' => new stdClass(), 'data' => (object) $data]), $response->getContent());
    }

    public function testNotFoundResponse(): void
    {
        $response = ResponseHelper::notFound();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals(json_encode(['status' => false, 'errors' => new stdClass(), 'data' => new stdClass()]), $response->getContent());
    }

    public function testNotAuthorizeResponse(): void
    {
        $response = ResponseHelper::notAuthorize();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertEquals(json_encode(['status' => false, 'errors' => new stdClass(), 'data' => new stdClass()]), $response->getContent());
    }

    public function testBadRequestResponse(): void
    {
        $errors = ['field' => 'Error message'];
        $response = ResponseHelper::badRequest($errors);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(json_encode(['status' => false, 'errors' => (object) $errors, 'data' => new stdClass()]), $response->getContent());
    }
}
