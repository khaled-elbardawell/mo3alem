<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use App\Models\ApiClient;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureTrustedApiClient
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiClient = $request->user();

        if (! $apiClient instanceof ApiClient || ! $apiClient->is_active) {
            return ApiResponse::error(
                message: 'عميل API غير مصرح له.',
                code: 'api_client_forbidden',
                status: Response::HTTP_FORBIDDEN,
                request: $request,
            );
        }

        if (! $apiClient->tokenCan(ApiClient::AbilityCreateUsers)) {
            return ApiResponse::error(
                message: 'التوكن لا يملك صلاحية إنشاء المستخدمين.',
                code: 'token_ability_missing',
                status: Response::HTTP_FORBIDDEN,
                request: $request,
            );
        }

        if (! $apiClient->acceptsIp((string) $request->ip())) {
            return ApiResponse::error(
                message: 'عنوان IP غير مصرح له.',
                code: 'ip_not_allowed',
                status: Response::HTTP_FORBIDDEN,
                request: $request,
            );
        }

        $requestId = $request->header('X-Request-ID');

        if (! is_string($requestId) || ! Str::isMatch('/\A[A-Za-z0-9._:-]{8,100}\z/', $requestId)) {
            $requestId = (string) Str::uuid();
        }

        $request->attributes->set('api_request_id', $requestId);
        Context::add('api_client_id', $apiClient->getKey());
        Context::add('api_request_id', $requestId);

        if (! $apiClient->last_used_at || $apiClient->last_used_at->lt(now()->subMinutes(5))) {
            $apiClient->forceFill(['last_used_at' => now()])->saveQuietly();
        }

        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
