<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\Project;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Determine whether the user can view any documents.
     */
    public function viewAny(User $user, Project $project): bool
    {
        // User must be a member of the project
        return $user->projects()->whereKey($project->id)->exists();
    }

    /**
     * Determine whether the user can view the document.
     */
    public function view(User $user, Document $document): bool
    {
        // User must be a member of the project
        return $user->projects()->whereKey($document->project_id)->exists();
    }

    /**
     * Determine whether the user can create documents.
     */
    public function create(User $user, Project $project): bool
    {
        // User must be a member of the project
        return $user->projects()->whereKey($project->id)->exists();
    }

    /**
     * Determine whether the user can delete the document.
     */
    public function delete(User $user, Document $document): bool
    {
        // User must be a member of the project OR be the uploader
        return $user->projects()->whereKey($document->project_id)->exists()
            || $document->uploaded_by === $user->id;
    }
}
