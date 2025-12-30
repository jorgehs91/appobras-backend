<?php

namespace App\Jobs;

use App\Http\Controllers\ExpenseController;
use App\Models\Project;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RecalculatePvxrJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting nightly PVxRV recalculation');

        $processed = 0;
        $errors = 0;

        // Process projects in chunks to avoid memory issues
        Project::query()
            ->whereHas('budget') // Only process projects with budgets
            ->chunk(100, function ($projects) use (&$processed, &$errors) {
                foreach ($projects as $project) {
                    try {
                        $this->recalculateProjectPvxr($project);
                        $processed++;
                    } catch (\Exception $e) {
                        $errors++;
                        Log::error('Error recalculating PVxRV for project', [
                            'project_id' => $project->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }
            });

        Log::info('Nightly PVxRV recalculation completed', [
            'processed' => $processed,
            'errors' => $errors,
        ]);
    }

    /**
     * Recalculate PVxRV cache for a specific project.
     */
    protected function recalculateProjectPvxr(Project $project): void
    {
        $cacheKey = "project_pvxr:{$project->id}";
        
        // Use the same calculation method from ExpenseController
        $controller = app(ExpenseController::class);
        $data = $controller->calculatePvxr($project);
        
        // Store in cache with 1 hour TTL
        Cache::put($cacheKey, $data, 3600);
    }
}

