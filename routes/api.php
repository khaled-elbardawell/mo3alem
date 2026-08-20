<?php

use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->middleware(['auth:sanctum', 'trusted-api-client', 'throttle:trusted-user-creation'])
    ->group(function (): void {
        Route::post('/users', UserController::class)->name('users.store');
    });
