<?php

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Jobs\SendAlertJob;
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

        // TODO: Query licenses expiring (when License model is available)
        $expiringLicenses = [];

        $usersWithOverdue = count(array_filter($overdueTasks, fn($tasks) => $tasks->isNotEmpty()));
        $usersWithNearDue = count(array_filter($nearDueTasks, fn($tasks) => $tasks->isNotEmpty()));

        $this->info("Found {$usersWithOverdue} users with overdue tasks");
        $this->info("Found {$usersWithNearDue} users with near-due tasks");

        // Dispatch queue jobs per user
        $usersProcessed = 0;
        $allUserIds = array_unique(array_merge(array_keys($overdueTasks), array_keys($nearDueTasks)));

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
