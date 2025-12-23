<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     *             @OA\Property(property="total_budget", type="number")
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

        return response()->json([
            'avg_progress' => $avgProgress,
            'overdue_tasks_count' => $overdueTasksCount,
            'upcoming_deliveries_count' => $upcomingDeliveriesCount,
            'total_budget' => (float) $totalBudget,
        ]);
    }
}

