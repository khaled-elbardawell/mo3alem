<?php

namespace App\Http\Responses;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class ApiExceptionResponse
{
    public function __invoke(Throwable $exception, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*')) {
            return null;
        }

        if ($exception instanceof ValidationException) {
            return ApiResponse::error(
                message: $exception->getMessage(),
                code: 'validation_error',
                status: $exception->status,
                errors: $exception->errors(),
                request: $request,
            );
        }

        if ($exception instanceof AuthenticationException) {
            return ApiResponse::error(
                message: 'المصادقة مطلوبة للوصول إلى هذا المورد.',
                code: 'unauthenticated',
                status: Response::HTTP_UNAUTHORIZED,
                request: $request,
            );
        }

        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
            $message = $exception->getMessage();

            if ($exception->getPrevious() instanceof ModelNotFoundException) {
                $message = 'المورد المطلوب غير موجود.';
            } elseif ($message === '') {
                $message = Response::$statusTexts[$status] ?? 'تعذر إكمال الطلب.';
            }

            return ApiResponse::error(
                message: $message,
                code: $this->codeForStatus($status),
                status: $status,
                headers: $exception->getHeaders(),
                request: $request,
            );
        }

        return ApiResponse::error(
            message: 'حدث خطأ غير متوقع.',
            code: 'server_error',
            status: Response::HTTP_INTERNAL_SERVER_ERROR,
            request: $request,
        );
    }

    private function codeForStatus(int $status): string
    {
        return match ($status) {
            Response::HTTP_BAD_REQUEST => 'bad_request',
            Response::HTTP_UNAUTHORIZED => 'unauthenticated',
            Response::HTTP_FORBIDDEN => 'forbidden',
            Response::HTTP_NOT_FOUND => 'not_found',
            Response::HTTP_METHOD_NOT_ALLOWED => 'method_not_allowed',
            Response::HTTP_CONFLICT => 'conflict',
            Response::HTTP_UNPROCESSABLE_CONTENT => 'validation_error',
            Response::HTTP_TOO_MANY_REQUESTS => 'too_many_requests',
            Response::HTTP_INTERNAL_SERVER_ERROR => 'server_error',
            Response::HTTP_SERVICE_UNAVAILABLE => 'service_unavailable',
            default => "http_{$status}",
        };
    }
}
