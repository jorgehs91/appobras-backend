<?php

namespace App\Models;

use App\Enums\PhaseStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Phase extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'project_id',
        'name',
        'description',
        'status',
        'sequence',
        'color',
        'planned_start_at',
        'planned_end_at',
        'actual_start_at',
        'actual_end_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PhaseStatus::class,
            'planned_start_at' => 'date',
            'planned_end_at' => 'date',
            'actual_start_at' => 'date',
            'actual_end_at' => 'date',
        ];
    }

    /**
     * Get the company that owns the phase.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the project that owns the phase.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the tasks for the phase.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Calculate progress percentage based on tasks.
     * Returns average of task progress (excluding canceled tasks).
     */
    public function getProgressPercentAttribute(): int
    {
        $tasks = $this->tasks()->whereNot('status', 'canceled')->get();
        
        if ($tasks->isEmpty()) {
            return 0;
        }
        
        $sum = $tasks->sum(fn($task) => $task->progress_percent);
        
        return (int) round($sum / $tasks->count());
    }

    /**
     * Get task counts by status.
     *
     * @return array<string, int>
     */
    public function getTasksCountsAttribute(): array
    {
        $tasks = $this->tasks()->get();
        
        return [
            'total' => $tasks->count(),
            'backlog' => $tasks->where('status', 'backlog')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'in_review' => $tasks->where('status', 'in_review')->count(),
            'done' => $tasks->where('status', 'done')->count(),
            'canceled' => $tasks->where('status', 'canceled')->count(),
        ];
    }
}

