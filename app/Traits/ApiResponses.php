<?php

namespace App\Traits;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;

trait ApiResponses
{
    protected function success(
        mixed $data = null,
        ?string $message = null,
        ?array $additionalMeta = null,
        int $statusCode = 200
    ): JsonResponse {
        return ApiResponse::success($data, $message, $additionalMeta, $statusCode);
    }

    protected function error(string $message, int $statusCode): JsonResponse
    {
        return ApiResponse::error($message, $statusCode);
    }

    protected function ok(mixed $data = null, ?string $message = null): JsonResponse
    {
        return ApiResponse::success($data, $message);
    }
}
