<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;

class WorkOrderPolicy
{
    /**
     * Determine whether the user can view any work orders.
     */
    public function viewAny(User $user, int $companyId): bool
    {
        // User must belong to the company and have budget access
        return $user->companies()->whereKey($companyId)->exists()
            && $user->hasBudgetAccess();
    }

    /**
     * Determine whether the user can view the work order.
     */
    public function view(User $user, WorkOrder $workOrder): bool
    {
        // User must belong to the company and have budget access
        $contract = $workOrder->contract;
        return $user->companies()->whereKey($contract->project->company_id)->exists()
            && $user->hasBudgetAccess();
    }

    /**
     * Determine whether the user can create work orders.
     */
    public function create(User $user, int $companyId): bool
    {
        // User must belong to the company and have budget access
        return $user->companies()->whereKey($companyId)->exists()
            && $user->hasBudgetAccess();
    }

    /**
     * Determine whether the user can update the work order.
     */
    public function update(User $user, WorkOrder $workOrder): bool
    {
        // User must belong to the company, have budget access
        // Work order must be editable (draft or canceled)
        $contract = $workOrder->contract;
        return $user->companies()->whereKey($contract->project->company_id)->exists()
            && $user->hasBudgetAccess()
            && in_array($workOrder->status->value, ['draft', 'canceled']);
    }

    /**
     * Determine whether the user can delete the work order.
     */
    public function delete(User $user, WorkOrder $workOrder): bool
    {
        // User must belong to the company, have budget access
        // Work order must be deletable (only draft)
        $contract = $workOrder->contract;
        return $user->companies()->whereKey($contract->project->company_id)->exists()
            && $user->hasBudgetAccess()
            && $workOrder->status->value === 'draft';
    }

    /**
     * Determine whether the user can approve the work order.
     */
    public function approve(User $user, WorkOrder $workOrder): bool
    {
        // User must belong to the company, have budget access
        // Work order must be in draft status
        $contract = $workOrder->contract;
        return $user->companies()->whereKey($contract->project->company_id)->exists()
            && $user->hasBudgetAccess()
            && $workOrder->status->value === 'draft';
    }
}

