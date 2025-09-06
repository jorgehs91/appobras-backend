<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // Auth public endpoints
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::middleware(['throttle:login'])->group(function (): void {
        Route::post('/auth/login', [AuthController::class, 'login']);
        Route::post('/auth/forgot', [AuthController::class, 'forgot']);
        Route::post('/auth/reset', [AuthController::class, 'reset']);
    });

    // Authenticated endpoints
    Route::middleware(['auth:sanctum'])->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('throttle:auth');
        Route::post('/auth/refresh', [AuthController::class, 'refresh'])->middleware('throttle:auth');
    });
});
