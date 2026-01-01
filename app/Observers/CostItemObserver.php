<?php

namespace App\Observers;

use App\Models\CostItem;
use Illuminate\Support\Facades\Cache;

class CostItemObserver
{
    /**
     * Handle the CostItem "created" event.
     */
    public function created(CostItem $costItem): void
    {
        $this->clearPvxrCache($costItem);
    }

    /**
     * Handle the CostItem "updated" event.
     */
    public function updated(CostItem $costItem): void
    {
        $this->clearPvxrCache($costItem);
    }

    /**
     * Handle the CostItem "deleted" event.
     */
    public function deleted(CostItem $costItem): void
    {
        $this->clearPvxrCache($costItem);
    }

    /**
     * Handle the CostItem "restored" event.
     */
    public function restored(CostItem $costItem): void
    {
        $this->clearPvxrCache($costItem);
    }

    /**
     * Clear PVxRV cache for the cost item's project.
     */
    protected function clearPvxrCache(CostItem $costItem): void
    {
        $budget = $costItem->budget;
        if ($budget && $budget->project_id) {
            $cacheKey = "project_pvxr:{$budget->project_id}";
            Cache::forget($cacheKey);

            // Also clear dashboard stats cache
            $this->clearDashboardCache($budget->project_id);
        }
    }

    /**
     * Clear dashboard stats cache for all users with access to the project.
     */
    protected function clearDashboardCache(int $projectId): void
    {
        \App\Http\Controllers\DashboardController::clearCacheForProject($projectId);
    }
}

