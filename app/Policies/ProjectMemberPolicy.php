<?php

namespace App\Policies;

use App\Enums\SystemRole;
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
        return $user->hasSystemRole(SystemRole::AdminObra)
            && $user->companies()->whereKey($project->company_id)->exists();
    }

    /**
     * Determine whether the user can create project members.
     */
    public function create(User $user, Project $project): bool
    {
        // Only Admin Obra can add members to projects
        return $user->hasSystemRole(SystemRole::AdminObra)
            && $user->companies()->whereKey($project->company_id)->exists();
    }

    /**
     * Determine whether the user can update project member roles.
     */
    public function update(User $user, Project $project): bool
    {
        // Only Admin Obra can update member roles
        return $user->hasSystemRole(SystemRole::AdminObra)
            && $user->companies()->whereKey($project->company_id)->exists();
    }

    /**
     * Determine whether the user can delete project members.
     */
    public function delete(User $user, Project $project): bool
    {
        // Only Admin Obra can remove members from projects
        return $user->hasSystemRole(SystemRole::AdminObra)
            && $user->companies()->whereKey($project->company_id)->exists();
    }
}
