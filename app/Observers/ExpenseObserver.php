<?php

namespace App\Observers;

use App\Models\Expense;
use Illuminate\Support\Facades\Cache;

class ExpenseObserver
{
    /**
     * Handle the Expense "created" event.
     */
    public function created(Expense $expense): void
    {
        $this->clearPvxrCache($expense);
    }

    /**
     * Handle the Expense "updated" event.
     */
    public function updated(Expense $expense): void
    {
        $this->clearPvxrCache($expense);
    }

    /**
     * Handle the Expense "deleted" event.
     */
    public function deleted(Expense $expense): void
    {
        $this->clearPvxrCache($expense);
    }

    /**
     * Handle the Expense "restored" event.
     */
    public function restored(Expense $expense): void
    {
        $this->clearPvxrCache($expense);
    }

    /**
     * Clear PVxRV cache for the expense's project.
     */
    protected function clearPvxrCache(Expense $expense): void
    {
        $cacheKey = "project_pvxr:{$expense->project_id}";
        Cache::forget($cacheKey);

        // Also clear dashboard stats cache for all users with access to this project
        $this->clearDashboardCache($expense->project_id);
    }

    /**
     * Clear dashboard stats cache for all users with access to the project.
     */
    protected function clearDashboardCache(int $projectId): void
    {
        \App\Http\Controllers\DashboardController::clearCacheForProject($projectId);
    }
}

