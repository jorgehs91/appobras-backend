<?php

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Jobs\SendAlertJob;
use App\Models\License;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AlertGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate alerts for overdue tasks and expiring licenses';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating alerts...');

        // Get configuration from .env with defaults
        $taskAlertDays = (int) config('app.alert_task_days', env('ALERT_TASK_DAYS', 3));
        $licenseAlertDays = (int) config('app.alert_license_days', env('ALERT_LICENSE_DAYS', 30));

        // Query overdue and near-due tasks
        $now = Carbon::now();
        $thresholdDate = $now->copy()->addDays($taskAlertDays);

        $tasksByUser = Task::query()
            ->whereNotNull('due_at')
            ->whereNotNull('assignee_id')
            ->where('due_at', '<=', $thresholdDate)
            ->where('status', '!=', TaskStatus::done)
            ->where('status', '!=', TaskStatus::canceled)
            ->with(['assignee', 'project'])
            ->get()
            ->groupBy('assignee_id');

        $overdueTasks = [];
        $nearDueTasks = [];

        foreach ($tasksByUser as $userId => $tasks) {
            $overdueTasks[$userId] = $tasks->filter(function ($task) use ($now) {
                return $task->due_at < $now;
            })->values();

            $nearDueTasks[$userId] = $tasks->filter(function ($task) use ($now, $taskAlertDays) {
                return $task->due_at >= $now && $task->due_at <= $now->copy()->addDays($taskAlertDays);
            })->values();
        }

        // Query licenses expiring
        $expiringLicensesByProject = License::query()
            ->expiringSoon($licenseAlertDays)
            ->with(['project.users', 'file'])
            ->get()
            ->groupBy('project_id');

        $expiringLicenses = [];

        foreach ($expiringLicensesByProject as $projectId => $licenses) {
            $project = $licenses->first()->project;
            
            if (!$project) {
                continue;
            }

            // Get all users from the project
            $projectUsers = $project->users()->get();

            foreach ($projectUsers as $user) {
                if (!isset($expiringLicenses[$user->id])) {
                    $expiringLicenses[$user->id] = [];
                }

                // Add licenses to user's list
                foreach ($licenses as $license) {
                    $expiringLicenses[$user->id][] = $license;
                }
            }
        }

        $usersWithOverdue = count(array_filter($overdueTasks, fn($tasks) => $tasks->isNotEmpty()));
        $usersWithNearDue = count(array_filter($nearDueTasks, fn($tasks) => $tasks->isNotEmpty()));
        $usersWithExpiringLicenses = count(array_filter($expiringLicenses, fn($licenses) => !empty($licenses)));

        $this->info("Found {$usersWithOverdue} users with overdue tasks");
        $this->info("Found {$usersWithNearDue} users with near-due tasks");
        $this->info("Found {$usersWithExpiringLicenses} users with expiring licenses");

        // Dispatch queue jobs per user
        $usersProcessed = 0;
        $allUserIds = array_unique(array_merge(
            array_keys($overdueTasks),
            array_keys($nearDueTasks),
            array_keys($expiringLicenses)
        ));

        foreach ($allUserIds as $userId) {
            $userOverdueTasks = $overdueTasks[$userId] ?? collect();
            $userNearDueTasks = $nearDueTasks[$userId] ?? collect();
            $userExpiringLicenses = $expiringLicenses[$userId] ?? [];

            // Only dispatch if there are alerts for this user
            if ($userOverdueTasks->isNotEmpty() || $userNearDueTasks->isNotEmpty() || !empty($userExpiringLicenses)) {
                SendAlertJob::dispatch(
                    $userId,
                    $userOverdueTasks,
                    $userNearDueTasks,
                    $userExpiringLicenses
                );

                $usersProcessed++;
            }
        }

        $this->info("Dispatched alert jobs for {$usersProcessed} users");
        $this->info('Alerts generated successfully.');
        
        return Command::SUCCESS;
    }
}
