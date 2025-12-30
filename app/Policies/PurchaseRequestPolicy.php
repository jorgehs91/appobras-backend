<?php

namespace App\Policies;

use App\Models\PurchaseRequest;
use App\Models\User;

class PurchaseRequestPolicy
{
    /**
     * Determine whether the user can view any purchase requests.
     */
    public function viewAny(User $user, int $companyId): bool
    {
        // User must belong to the company and have budget access
        return $user->companies()->whereKey($companyId)->exists()
            && $user->hasBudgetAccess();
    }

    /**
     * Determine whether the user can view the purchase request.
     */
    public function view(User $user, PurchaseRequest $purchaseRequest): bool
    {
        // User must belong to the company, have budget access, and be a member of the project
        return $user->companies()->whereKey($purchaseRequest->project->company_id)->exists()
            && $user->hasBudgetAccess()
            && $user->projects()->whereKey($purchaseRequest->project_id)->exists();
    }

    /**
     * Determine whether the user can create purchase requests.
     */
    public function create(User $user, int $companyId): bool
    {
        // User must belong to the company and have budget access
        return $user->companies()->whereKey($companyId)->exists()
            && $user->hasBudgetAccess();
    }

    /**
     * Determine whether the user can update the purchase request.
     */
    public function update(User $user, PurchaseRequest $purchaseRequest): bool
    {
        // User must belong to the company, have budget access, and be a member of the project
        // Also, PR must be editable (draft or rejected)
        return $user->companies()->whereKey($purchaseRequest->project->company_id)->exists()
            && $user->hasBudgetAccess()
            && $user->projects()->whereKey($purchaseRequest->project_id)->exists()
            && $purchaseRequest->canBeEdited();
    }

    /**
     * Determine whether the user can delete the purchase request.
     */
    public function delete(User $user, PurchaseRequest $purchaseRequest): bool
    {
        // User must belong to the company, have budget access, and be a member of the project
        // Also, PR must be deletable (only draft)
        return $user->companies()->whereKey($purchaseRequest->project->company_id)->exists()
            && $user->hasBudgetAccess()
            && $user->projects()->whereKey($purchaseRequest->project_id)->exists()
            && $purchaseRequest->canBeDeleted();
    }

    /**
     * Determine whether the user can submit the purchase request.
     */
    public function submit(User $user, PurchaseRequest $purchaseRequest): bool
    {
        // User must belong to the company, have budget access, and be a member of the project
        // PR must be in draft status
        return $user->companies()->whereKey($purchaseRequest->project->company_id)->exists()
            && $user->hasBudgetAccess()
            && $user->projects()->whereKey($purchaseRequest->project_id)->exists()
            && $purchaseRequest->status->value === 'draft';
    }

    /**
     * Determine whether the user can approve the purchase request.
     */
    public function approve(User $user, PurchaseRequest $purchaseRequest): bool
    {
        // User must belong to the company, have budget access, and be a member of the project
        // PR must be in submitted status
        return $user->companies()->whereKey($purchaseRequest->project->company_id)->exists()
            && $user->hasBudgetAccess()
            && $user->projects()->whereKey($purchaseRequest->project_id)->exists()
            && $purchaseRequest->status->value === 'submitted';
    }

    /**
     * Determine whether the user can reject the purchase request.
     */
    public function reject(User $user, PurchaseRequest $purchaseRequest): bool
    {
        // User must belong to the company, have budget access, and be a member of the project
        // PR must be in submitted status
        return $user->companies()->whereKey($purchaseRequest->project->company_id)->exists()
            && $user->hasBudgetAccess()
            && $user->projects()->whereKey($purchaseRequest->project_id)->exists()
            && $purchaseRequest->status->value === 'submitted';
    }
}

