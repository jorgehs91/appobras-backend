<?php

namespace App\Http\Controllers;

use App\Enums\ExpenseStatus;
use App\Models\CostItem;
use App\Models\Expense;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * @group Dashboard e Relatórios
 *
 * Endpoints relacionados a estatísticas e dashboard.
 */
class DashboardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/stats",
     *     summary="Estatísticas do dashboard",
     *     description="Retorna estatísticas agregadas dos projetos",
     *     tags={"Progress"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Estatísticas",
     *         @OA\JsonContent(
     *             @OA\Property(property="avg_progress", type="integer"),
     *             @OA\Property(property="overdue_tasks_count", type="integer"),
     *             @OA\Property(property="upcoming_deliveries_count", type="integer"),
     *             @OA\Property(property="total_budget", type="number"),
     *             @OA\Property(
     *                 property="pvxr_summary",
     *                 type="object",
     *                 @OA\Property(property="total_planned", type="number"),
     *                 @OA\Property(property="total_realized", type="number"),
     *                 @OA\Property(property="variance", type="number"),
     *                 @OA\Property(property="variance_percentage", type="number")
     *             ),
     *             @OA\Property(
     *                 property="expiring_licenses",
     *                 type="object",
     *                 @OA\Property(property="expiring_count", type="integer"),
     *                 @OA\Property(property="expiring_soon_count", type="integer"),
     *                 @OA\Property(property="days_threshold", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function stats(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);

        // Generate cache key based on user, company, and optional project filter
        $cacheKey = $this->getCacheKey($user->id, $companyId, $request->input('project_id'));

        // Cache for 10 minutes (600 seconds)
        $stats = Cache::remember($cacheKey, 600, function () use ($user, $companyId, $request) {
            return $this->calculateStats($user, $companyId, $request);
        });

        return response()->json($stats);
    }

    /**
     * Calculate dashboard statistics.
     *
     * @param  \App\Models\User  $user
     * @param  int  $companyId
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    private function calculateStats($user, int $companyId, Request $request): array
    {
        // Get projects the user has access to
        $projectsQuery = Project::query()
            ->where('company_id', $companyId)
            ->whereHas('users', function ($q) use ($user): void {
                $q->whereKey($user->id);
            });

        // Filter by specific project if provided
        if ($request->has('project_id')) {
            $projectsQuery->whereKey((int) $request->input('project_id'));
        }

        $projects = $projectsQuery->with(['phases' => function ($q): void {
            $q->where('status', 'active')->with('tasks');
        }])->get();

        // Calculate average progress across all projects
        $avgProgress = 0;
        if ($projects->isNotEmpty()) {
            $progressSum = $projects->sum(fn($project) => $project->progress_percent);
            $avgProgress = (int) round($progressSum / $projects->count());
        }

        // Get all tasks from these projects
        $projectIds = $projects->pluck('id');

        // Overdue tasks: planned_end_at < today and status != done
        $overdueTasksCount = Task::query()
            ->whereIn('project_id', $projectIds)
            ->where('planned_end_at', '<', now()->toDateString())
            ->where('status', '!=', 'done')
            ->count();

        // Upcoming deliveries: due_at in the next 7 days
        $upcomingDeliveriesCount = Task::query()
            ->whereIn('project_id', $projectIds)
            ->whereBetween('due_at', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->where('status', '!=', 'done')
            ->count();

        // Budget stats (if available in projects)
        $totalBudget = $projects->sum('planned_budget_amount') ?? 0;

        // Calculate PVxRV summary for all accessible projects
        $pvxrSummary = $this->calculatePvxrSummary($projectIds);

        // Get expiring licenses count (placeholder - License model not yet implemented)
        $expiringLicenses = $this->getExpiringLicenses($projectIds);

        return [
            'avg_progress' => $avgProgress,
            'overdue_tasks_count' => $overdueTasksCount,
            'upcoming_deliveries_count' => $upcomingDeliveriesCount,
            'total_budget' => (float) $totalBudget,
            'pvxr_summary' => $pvxrSummary,
            'expiring_licenses' => $expiringLicenses,
        ];
    }

    /**
     * Generate cache key for dashboard stats.
     *
     * @param  int  $userId
     * @param  int  $companyId
     * @param  int|null  $projectId
     * @return string
     */
    private function getCacheKey(int $userId, int $companyId, ?int $projectId = null): string
    {
        $key = "dashboard.stats:user:{$userId}:company:{$companyId}";
        
        if ($projectId !== null) {
            $key .= ":project:{$projectId}";
        }

        return $key;
    }

    /**
     * Clear dashboard stats cache for a specific user and company.
     * Can be called from observers or other parts of the application.
     *
     * @param  int  $userId
     * @param  int  $companyId
     * @param  int|null  $projectId
     * @return void
     */
    public static function clearCache(int $userId, int $companyId, ?int $projectId = null): void
    {
        $controller = new self();
        $cacheKey = $controller->getCacheKey($userId, $companyId, $projectId);
        Cache::forget($cacheKey);

        // Also clear the general cache (without project filter) for this user/company
        if ($projectId !== null) {
            $generalKey = $controller->getCacheKey($userId, $companyId, null);
            Cache::forget($generalKey);
        }
    }

    /**
     * Clear dashboard stats cache for all users with access to a project.
     * More efficient than clearing per user when we know a project changed.
     *
     * @param  int  $projectId
     * @return void
     */
    public static function clearCacheForProject(int $projectId): void
    {
        $project = Project::with('users', 'company')->find($projectId);
        if (!$project) {
            return;
        }

        $companyId = $project->company_id;
        $userIds = $project->users()->pluck('users.id')->toArray();

        foreach ($userIds as $userId) {
            // Clear cache with project filter
            self::clearCache($userId, $companyId, $projectId);
            // Clear general cache (all projects)
            self::clearCache($userId, $companyId, null);
        }
    }

    /**
     * Calculate PVxRV summary aggregated across multiple projects.
     *
     * @param  \Illuminate\Support\Collection<int, int>  $projectIds
     * @return array<string, float>
     */
    private function calculatePvxrSummary($projectIds): array
    {
        if ($projectIds->isEmpty()) {
            return [
                'total_planned' => 0.0,
                'total_realized' => 0.0,
                'variance' => 0.0,
                'variance_percentage' => 0.0,
            ];
        }

        // Get all budgets for these projects
        $budgets = DB::table('budgets')
            ->whereIn('project_id', $projectIds)
            ->get();

        if ($budgets->isEmpty()) {
            return [
                'total_planned' => 0.0,
                'total_realized' => 0.0,
                'variance' => 0.0,
                'variance_percentage' => 0.0,
            ];
        }

        $budgetIds = $budgets->pluck('id');

        // Calculate total planned from cost items
        $totalPlanned = (float) CostItem::query()
            ->whereIn('budget_id', $budgetIds)
            ->sum('planned_amount');

        // Calculate total realized from approved expenses
        $totalRealized = (float) Expense::query()
            ->whereIn('project_id', $projectIds)
            ->where('status', ExpenseStatus::approved)
            ->sum('amount');

        $variance = $totalPlanned - $totalRealized;
        $variancePercentage = $totalPlanned > 0 ? ($variance / $totalPlanned) * 100 : 0;

        return [
            'total_planned' => round($totalPlanned, 2),
            'total_realized' => round($totalRealized, 2),
            'variance' => round($variance, 2),
            'variance_percentage' => round($variancePercentage, 2),
        ];
    }

    /**
     * Get expiring licenses count (placeholder - License model not yet implemented).
     *
     * TODO: Implement when License model is available.
     * Query should be: WHERE expiry_date <= NOW() + INTERVAL days_threshold DAY
     *
     * @param  \Illuminate\Support\Collection<int, int>  $projectIds
     * @return array<string, int>
     */
    private function getExpiringLicenses($projectIds): array
    {
        // Placeholder: License model not yet implemented
        // When License model is available, implement query:
        // License::whereIn('project_id', $projectIds)
        //     ->where('expiry_date', '<=', now()->addDays(30))
        //     ->where('expiry_date', '>=', now())
        //     ->count();

        $daysThreshold = (int) config('app.alert_license_days', 30);

        return [
            'expiring_count' => 0,
            'expiring_soon_count' => 0,
            'days_threshold' => $daysThreshold,
        ];
    }
}

