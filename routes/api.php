<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\MeController;
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

        // Companies
        Route::get('/companies', [CompanyController::class, 'index']);
        Route::post('/companies', [CompanyController::class, 'store']);

        // Invites
        Route::post('/companies/{company}/invites', [InviteController::class, 'create'])->middleware(['company', 'permission:users.update,sanctum']);
        Route::post('/invites/{token}/accept', [InviteController::class, 'accept']);

        // Me
        Route::post('/me/switch-company', [MeController::class, 'switchCompany']);
        Route::post('/me/switch-project', [MeController::class, 'switchProject'])->middleware(['company']);

        // Admin-only RBAC management (guard sanctum + contexto de empresa)
        Route::prefix('admin')->middleware(['company'])->group(function (): void {
            Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:users.update,sanctum');
            Route::get('/permissions', [RoleController::class, 'permissions'])->middleware('permission:users.update,sanctum');
            Route::post('/roles/{role}/assign', [RoleController::class, 'assign'])->middleware('permission:users.update,sanctum');
            Route::post('/roles/{role}/revoke', [RoleController::class, 'revoke'])->middleware('permission:users.update,sanctum');
        });

        // Projects (escopo por company obrigatório, por project opcional via header)
        Route::middleware(['company'])->group(function (): void {
            Route::get('/projects', [ProjectController::class, 'index']);
            Route::post('/projects', [ProjectController::class, 'store']);
        });
    });
});
