<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'تمت العملية بنجاح.',
        int $status = Response::HTTP_OK,
        ?Request $request = null,
    ): JsonResponse {
        return self::make(
            success: true,
            message: $message,
            data: $data,
            status: $status,
            request: $request,
        );
    }

    /**
     * @param  array<string, mixed>|null  $errors
     * @param  array<string, string|string[]>  $headers
     */
    public static function error(
        string $message,
        string $code = 'error',
        int $status = Response::HTTP_BAD_REQUEST,
        ?array $errors = null,
        array $headers = [],
        ?Request $request = null,
    ): JsonResponse {
        return self::make(
            success: false,
            message: $message,
            errors: $errors,
            code: $code,
            status: $status,
            headers: $headers,
            request: $request,
        );
    }

    /**
     * @param  array<string, mixed>|null  $errors
     * @param  array<string, string|string[]>  $headers
     */
    private static function make(
        bool $success,
        string $message,
        mixed $data = null,
        ?array $errors = null,
        ?string $code = null,
        int $status = Response::HTTP_OK,
        array $headers = [],
        ?Request $request = null,
    ): JsonResponse {
        $request ??= request();
        $requestId = self::requestId($request);

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
            'code' => $code,
            'request_id' => $requestId,
        ], $status, array_merge([
            'X-Request-ID' => $requestId,
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
        ], $headers));
    }

    private static function requestId(Request $request): string
    {
        $requestId = $request->attributes->get('api_request_id');

        if (is_string($requestId) && Str::isMatch('/\A[A-Za-z0-9._:-]{8,100}\z/', $requestId)) {
            return $requestId;
        }

        $requestId = $request->header('X-Request-ID');

        if (! is_string($requestId) || ! Str::isMatch('/\A[A-Za-z0-9._:-]{8,100}\z/', $requestId)) {
            $requestId = (string) Str::uuid();
        }

        $request->attributes->set('api_request_id', $requestId);

        return $requestId;
    }
}
