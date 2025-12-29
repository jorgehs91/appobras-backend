<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\PhaseController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMembersController;
use App\Http\Controllers\ProjectProgressController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskDependencyController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CostItemController;
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
        Route::post('/invites/project/{token}/accept', [InviteController::class, 'acceptProjectInvite']);

        // Me
        Route::post('/me/switch-company', [MeController::class, 'switchCompany']);
        Route::post('/me/switch-project', [MeController::class, 'switchProject'])->middleware(['company']);
        Route::put('/user/preferences', [MeController::class, 'updatePreferences']);

        // Admin-only RBAC management (guard sanctum + contexto de empresa)
        Route::prefix('admin')->middleware(['company'])->group(function (): void {
            Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:users.update,sanctum');
            Route::get('/permissions', [RoleController::class, 'permissions'])->middleware('permission:users.update,sanctum');
            Route::post('/roles/{role}/assign', [RoleController::class, 'assign'])->middleware('permission:users.update,sanctum');
            Route::post('/roles/{role}/revoke', [RoleController::class, 'revoke'])->middleware('permission:users.update,sanctum');
            Route::get('/audit-logs', [AuditLogController::class, 'index'])->middleware('permission:users.update,sanctum');
        });

        // Projects (escopo por company obrigatório, por project opcional via header)
        Route::middleware(['company'])->group(function (): void {
            Route::get('/projects', [ProjectController::class, 'index']);
            Route::post('/projects', [ProjectController::class, 'store']);
            Route::get('/projects/{project}', [ProjectController::class, 'show']);
            Route::match(['put', 'patch'], '/projects/{project}', [ProjectController::class, 'update']);

            // Contractors (escopo company)
            Route::get('/contractors', [ContractorController::class, 'index']);
            Route::post('/contractors', [ContractorController::class, 'store']);
            Route::put('/contractors/{contractor}', [ContractorController::class, 'update']);
            Route::delete('/contractors/{contractor}', [ContractorController::class, 'destroy']);

            // Phases (escopo project)
            Route::get('/projects/{project}/phases', [PhaseController::class, 'index']);
            Route::post('/projects/{project}/phases', [PhaseController::class, 'store']);
            Route::put('/phases/{phase}', [PhaseController::class, 'update']);
            Route::delete('/phases/{phase}', [PhaseController::class, 'destroy']);

            // Tasks (escopo project)
            Route::get('/projects/{project}/tasks', [TaskController::class, 'index']);
            Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
            Route::patch('/projects/{project}/tasks/bulk', [TaskController::class, 'bulkUpdate']);
            Route::put('/tasks/{task}', [TaskController::class, 'update']);
            Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
            Route::patch('/tasks/{task}/dependencies', [TaskDependencyController::class, 'updateBulk']);
            Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

            // Task Dependencies (escopo project)
            Route::get('/task-dependencies', [TaskDependencyController::class, 'index']);
            Route::post('/projects/{project}/task-dependencies', [TaskDependencyController::class, 'store']);
            Route::post('/projects/{project}/task-dependencies/bulk', [TaskDependencyController::class, 'storeBulk']);
            Route::put('/task-dependencies/{taskDependency}', [TaskDependencyController::class, 'update']);
            Route::delete('/task-dependencies/{taskDependency}', [TaskDependencyController::class, 'destroy']);

            // Documents (escopo project)
            Route::get('/projects/{project}/documents', [DocumentController::class, 'index']);
            Route::post('/projects/{project}/documents', [DocumentController::class, 'store']);
            Route::get('/documents/{document}', [DocumentController::class, 'show']);
            Route::get('/documents/{document}/download', [DocumentController::class, 'download']);
            Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);

            // Project Members (escopo project)
            Route::get('/projects/{project}/members', [ProjectMembersController::class, 'index']);
            Route::post('/projects/{project}/members', [ProjectMembersController::class, 'store']);
            Route::patch('/projects/{project}/members/{userId}', [ProjectMembersController::class, 'update']);
            Route::delete('/projects/{project}/members/{userId}', [ProjectMembersController::class, 'destroy']);

            // Progress & Stats
            Route::get('/projects/{project}/progress', [ProjectProgressController::class, 'show']);
            Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

            // Budgets (escopo project)
            Route::get('/projects/{project}/budgets', [BudgetController::class, 'index']);
            Route::post('/projects/{project}/budgets', [BudgetController::class, 'store']);
            Route::get('/projects/{project}/budget/summary', [BudgetController::class, 'summary']);
            Route::get('/budgets/{budget}', [BudgetController::class, 'show']);
            Route::match(['put', 'patch'], '/budgets/{budget}', [BudgetController::class, 'update']);
            Route::delete('/budgets/{budget}', [BudgetController::class, 'destroy']);

            // Cost Items (escopo budget/project)
            Route::get('/projects/{project}/budgets/{budget}/cost-items', [CostItemController::class, 'index']);
            Route::post('/projects/{project}/budgets/{budget}/cost-items', [CostItemController::class, 'store']);
            Route::get('/cost-items/{costItem}', [CostItemController::class, 'show']);
            Route::match(['put', 'patch'], '/cost-items/{costItem}', [CostItemController::class, 'update']);
            Route::delete('/cost-items/{costItem}', [CostItemController::class, 'destroy']);
        });
    });
});
