<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait InteractsWithApiResponses
{
    protected function apiSuccess(
        mixed $data = null,
        string $message = 'تمت العملية بنجاح.',
        int $status = Response::HTTP_OK,
    ): JsonResponse {
        return ApiResponse::success($data, $message, $status);
    }

    /** @param array<string, mixed>|null $errors */
    protected function apiError(
        string $message,
        string $code = 'error',
        int $status = Response::HTTP_BAD_REQUEST,
        ?array $errors = null,
    ): JsonResponse {
        return ApiResponse::error($message, $code, $status, $errors);
    }
}
