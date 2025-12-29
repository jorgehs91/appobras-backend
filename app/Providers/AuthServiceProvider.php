<?php

namespace App\Providers;

use App\Enums\SystemRole;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Phase::class => \App\Policies\PhasePolicy::class,
        \App\Models\Task::class => \App\Policies\TaskPolicy::class,
        \App\Models\Contractor::class => \App\Policies\ContractorPolicy::class,
        \App\Models\Document::class => \App\Policies\DocumentPolicy::class,
        \App\Models\Project::class => \App\Policies\ProjectMemberPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('admin-only', function (User $user): bool {
            return $user->hasSystemRole(SystemRole::AdminObra);
        });

        Gate::define('budget-access', function (User $user): bool {
            return $user->hasBudgetAccess();
        });
    }
}


