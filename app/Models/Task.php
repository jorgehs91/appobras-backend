<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Traits\AuditTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes, AuditTrait;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'project_id',
        'phase_id',
        'title',
        'description',
        'status',
        'priority',
        'order_in_phase',
        'assignee_id',
        'contractor_id',
        'is_blocked',
        'blocked_reason',
        'planned_start_at',
        'planned_end_at',
        'due_at',
        'started_at',
        'completed_at',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'is_blocked' => 'boolean',
            'planned_start_at' => 'date',
            'planned_end_at' => 'date',
            'due_at' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the company that owns the task.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the project that owns the task.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the phase that owns the task.
     */
    public function phase()
    {
        return $this->belongsTo(Phase::class);
    }

    /**
     * Get the user assigned to this task.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Get the contractor assigned to this task.
     */
    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    /**
     * Get the user who created the task.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the task.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the comments for this task.
     */
    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }

    /**
     * Calculate progress percentage based on status.
     * Mapping: backlog=0, in_progress=50, in_review=90, done=100, canceled=0
     */
    public function getProgressPercentAttribute(): int
    {
        return match($this->status) {
            TaskStatus::backlog => 0,
            TaskStatus::in_progress => 50,
            TaskStatus::in_review => 90,
            TaskStatus::done => 100,
            TaskStatus::canceled => 0,
            default => 0,
        };
    }
}

