<?php

declare(strict_types=1);

namespace Tizix\LaravelHelpers\Helpers\Response;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ResponseHelper
{
    private static function responsePrams($status, $errors = [], $data = []): array
    {
        return [
            'status' => $status,
            'errors' => $errors,
            'data' => $data,
        ];
    }

    public static function sendJsonResponse($status, $code = Response::HTTP_OK, $errors = [], $data = []): JsonResponse
    {
        return response()->json(
            self::responsePrams($status, $errors, $data),
            $code
        );
    }

    public static function success($data = []): JsonResponse
    {
        return self::sendJsonResponse(true, Response::HTTP_OK, [], $data);
    }

    public static function notFound($data = []): JsonResponse
    {
        return self::sendJsonResponse(false, Response::HTTP_NOT_FOUND, $data, []);
    }

    public static function notAuthorize(): JsonResponse
    {
        return self::sendJsonResponse(false, Response::HTTP_UNAUTHORIZED, [], []);
    }

    public static function badRequest($data = []): JsonResponse
    {
        return self::sendJsonResponse(false, Response::HTTP_BAD_REQUEST, $data, []);
    }

    public static function unauthorized($data = []): JsonResponse
    {
        return self::sendJsonResponse(false, Response::HTTP_UNAUTHORIZED, $data, []);
    }

    public static function forbidden($data = []): JsonResponse
    {
        return self::sendJsonResponse(false, Response::HTTP_FORBIDDEN, $data, []);
    }

    public static function conflict($data = []): JsonResponse
    {
        return self::sendJsonResponse(false, Response::HTTP_CONFLICT, $data, []);
    }

    public static function created($data = []): JsonResponse
    {
        return self::sendJsonResponse(false, Response::HTTP_CREATED, [], $data);
    }
    public static function noContent($data = []): JsonResponse
    {
        return self::sendJsonResponse(false, Response::HTTP_NO_CONTENT, $data, []);
    }

}
