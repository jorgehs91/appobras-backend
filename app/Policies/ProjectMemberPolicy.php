<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectMemberPolicy
{
    /**
     * Determine whether the user can view any project members.
     */
    public function viewAny(User $user, Project $project): bool
    {
        // Only Admin Obra can view project members
        return $user->hasRole('Admin Obra')
            && $user->companies()->whereKey($project->company_id)->exists();
    }

    /**
     * Determine whether the user can create project members.
     */
    public function create(User $user, Project $project): bool
    {
        // Only Admin Obra can add members to projects
        return $user->hasRole('Admin Obra')
            && $user->companies()->whereKey($project->company_id)->exists();
    }

    /**
     * Determine whether the user can update project member roles.
     */
    public function update(User $user, Project $project): bool
    {
        // Only Admin Obra can update member roles
        return $user->hasRole('Admin Obra')
            && $user->companies()->whereKey($project->company_id)->exists();
    }

    /**
     * Determine whether the user can delete project members.
     */
    public function delete(User $user, Project $project): bool
    {
        // Only Admin Obra can remove members from projects
        return $user->hasRole('Admin Obra')
            && $user->companies()->whereKey($project->company_id)->exists();
    }
}
