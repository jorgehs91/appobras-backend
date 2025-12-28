<?php

namespace App\Jobs;

use App\Mail\ProjectInvitationMail;
use App\Models\Project;
use App\Models\ProjectInvite;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendProjectInvitationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ProjectInvite $invite,
        public Project $project,
        public User $user
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user->email)->send(
            new ProjectInvitationMail($this->invite, $this->project, $this->user)
        );
    }
}
