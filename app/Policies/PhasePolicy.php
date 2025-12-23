<?php

namespace App\Policies;

use App\Models\Phase;
use App\Models\Project;
use App\Models\User;

class PhasePolicy
{
    /**
     * Determine whether the user can view any phases.
     */
    public function viewAny(User $user, Project $project): bool
    {
        // User must belong to the company and be a member of the project
        return $user->companies()->whereKey($project->company_id)->exists()
            && $user->projects()->whereKey($project->id)->exists();
    }

    /**
     * Determine whether the user can view the phase.
     */
    public function view(User $user, Phase $phase): bool
    {
        // User must be a member of the project
        return $user->projects()->whereKey($phase->project_id)->exists();
    }

    /**
     * Determine whether the user can create phases.
     */
    public function create(User $user, Project $project): bool
    {
        // User must be a member of the project
        return $user->projects()->whereKey($project->id)->exists();
    }

    /**
     * Determine whether the user can update the phase.
     */
    public function update(User $user, Phase $phase): bool
    {
        // User must be a member of the project
        return $user->projects()->whereKey($phase->project_id)->exists();
    }

    /**
     * Determine whether the user can delete the phase.
     */
    public function delete(User $user, Phase $phase): bool
    {
        // User must be a member of the project
        return $user->projects()->whereKey($phase->project_id)->exists();
    }
}
