<?php

namespace App\Policies;

use App\Models\Contractor;
use App\Models\User;

class ContractorPolicy
{
    /**
     * Determine whether the user can view any contractors.
     */
    public function viewAny(User $user, int $companyId): bool
    {
        // User must belong to the company
        return $user->companies()->whereKey($companyId)->exists();
    }

    /**
     * Determine whether the user can view the contractor.
     */
    public function view(User $user, Contractor $contractor): bool
    {
        // User must belong to the same company
        return $user->companies()->whereKey($contractor->company_id)->exists();
    }

    /**
     * Determine whether the user can create contractors.
     */
    public function create(User $user, int $companyId): bool
    {
        // User must belong to the company
        return $user->companies()->whereKey($companyId)->exists();
    }

    /**
     * Determine whether the user can update the contractor.
     */
    public function update(User $user, Contractor $contractor): bool
    {
        // User must belong to the same company
        return $user->companies()->whereKey($contractor->company_id)->exists();
    }

    /**
     * Determine whether the user can delete the contractor.
     */
    public function delete(User $user, Contractor $contractor): bool
    {
        // User must belong to the same company
        return $user->companies()->whereKey($contractor->company_id)->exists();
    }
}
