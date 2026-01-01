<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\PhaseController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMembersController;
use App\Http\Controllers\ProjectProgressController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskDependencyController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Api\TaskCommentController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CostItemController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\LicenseController;
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
        Route::post('/user/expo-token', [MeController::class, 'updateExpoToken']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

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

            // Contracts (escopo contractor)
            Route::get('/contractors/{contractor}/contracts', [ContractController::class, 'index']);
            Route::post('/contractors/{contractor}/contracts', [ContractController::class, 'store']);
            Route::get('/contractors/{contractor}/contracts/{contract}', [ContractController::class, 'show']);
            Route::put('/contractors/{contractor}/contracts/{contract}', [ContractController::class, 'update']);
            Route::delete('/contractors/{contractor}/contracts/{contract}', [ContractController::class, 'destroy']);

            // Work Orders (escopo contractor)
            Route::get('/contractors/{contractor}/work-orders', [WorkOrderController::class, 'index']);
            Route::post('/contractors/{contractor}/work-orders', [WorkOrderController::class, 'store']);
            Route::get('/contractors/{contractor}/work-orders/{workOrder}', [WorkOrderController::class, 'show']);
            Route::put('/contractors/{contractor}/work-orders/{workOrder}', [WorkOrderController::class, 'update']);
            Route::delete('/contractors/{contractor}/work-orders/{workOrder}', [WorkOrderController::class, 'destroy']);
            Route::post('/contractors/{contractor}/work-orders/{workOrder}/approve', [WorkOrderController::class, 'approve']);

            // Payments (escopo contractor)
            Route::get('/contractors/{contractor}/payments', [PaymentController::class, 'index']);
            Route::get('/contractors/{contractor}/payments/pending', [PaymentController::class, 'pending']);
            Route::post('/contractors/{contractor}/payments', [PaymentController::class, 'store']);
            Route::get('/contractors/{contractor}/payments/{payment}', [PaymentController::class, 'show']);
            Route::put('/contractors/{contractor}/payments/{payment}', [PaymentController::class, 'update']);
            Route::delete('/contractors/{contractor}/payments/{payment}', [PaymentController::class, 'destroy']);

            // Suppliers (escopo company)
            Route::get('/suppliers', [SupplierController::class, 'index']);
            Route::get('/suppliers/{supplier}', [SupplierController::class, 'show']);
            Route::post('/suppliers', [SupplierController::class, 'store']);
            Route::put('/suppliers/{supplier}', [SupplierController::class, 'update']);
            Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy']);

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

            // Task Attachments (escopo project via task)
            Route::get('/tasks/{task}/attachments', [AttachmentController::class, 'index']);
            Route::post('/tasks/{task}/attachments', [AttachmentController::class, 'store']);
            Route::get('/attachments/{attachment}', [AttachmentController::class, 'show']);
            Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download']);
            Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy']);

            // Task Comments (escopo project via task)
            Route::get('/tasks/{task}/comments', [TaskCommentController::class, 'index']);
            Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store']);
            Route::get('/tasks/{task}/comments/{comment}', [TaskCommentController::class, 'show']);
            Route::put('/tasks/{task}/comments/{comment}', [TaskCommentController::class, 'update']);
            Route::delete('/tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy']);

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

            // Expenses (escopo project)
            Route::get('/projects/{project}/expenses', [ExpenseController::class, 'index']);
            Route::post('/projects/{project}/expenses', [ExpenseController::class, 'store']);
            Route::get('/projects/{project}/pvxr', [ExpenseController::class, 'pvxr']);
            Route::get('/expenses/{expense}', [ExpenseController::class, 'show']);
            Route::match(['put', 'patch'], '/expenses/{expense}', [ExpenseController::class, 'update']);
            Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy']);
            Route::get('/expenses/{expense}/receipt', [ExpenseController::class, 'downloadReceipt']);

            // Purchase Requests (escopo project)
            Route::get('/projects/{project}/purchase-requests', [PurchaseRequestController::class, 'index']);
            Route::post('/projects/{project}/purchase-requests', [PurchaseRequestController::class, 'store']);
            Route::get('/purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'show']);
            Route::put('/purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'update']);
            Route::delete('/purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'destroy']);
            Route::post('/purchase-requests/{purchaseRequest}/submit', [PurchaseRequestController::class, 'submit']);
            Route::post('/purchase-requests/{purchaseRequest}/approve', [PurchaseRequestController::class, 'approve']);
            Route::post('/purchase-requests/{purchaseRequest}/reject', [PurchaseRequestController::class, 'reject']);

            // Licenses (escopo project/company)
            Route::get('/licenses', [LicenseController::class, 'index']);
            Route::get('/licenses/expiring', [LicenseController::class, 'expiring']);
            Route::post('/licenses', [LicenseController::class, 'store']);
            Route::get('/licenses/{license}', [LicenseController::class, 'show']);
            Route::put('/licenses/{license}', [LicenseController::class, 'update']);
            Route::delete('/licenses/{license}', [LicenseController::class, 'destroy']);
        });
    });
});
