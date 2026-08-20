<?php

use App\Http\Middleware\EnsurePasswordIsChanged;
use App\Http\Middleware\EnsureTrustedApiClient;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Responses\ApiExceptionResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'admin' => EnsureUserIsAdmin::class,
            'password.changed' => EnsurePasswordIsChanged::class,
            'trusted-api-client' => EnsureTrustedApiClient::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(
            fn (Throwable $exception, Request $request) => (new ApiExceptionResponse)($exception, $request),
        );

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('saved-wheels*')
                || $request->is('api/*')
                || $request->is('qr-codes*')
                || $request->is('certificates*')
                || $request->is('tools/qr/render')
                || $request->is('activity-metrics')
                || $request->expectsJson(),
        );
    })->create();
