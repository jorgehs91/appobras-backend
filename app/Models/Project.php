<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'status',
        'archived_at',
        'start_date',
        'end_date',
        'actual_start_date',
        'actual_end_date',
        'planned_budget_amount',
        'manager_user_id',
        'address',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'archived_at' => 'datetime',
            'start_date' => 'date',
            'end_date' => 'date',
            'actual_start_date' => 'date',
            'actual_end_date' => 'date',
            'planned_budget_amount' => 'decimal:2',
        ];
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->using(ProjectMember::class)
            ->withPivot(['role', 'joined_at', 'preferences'])
            ->withTimestamps();
    }

    public function memberships()
    {
        return $this->hasMany(ProjectMember::class, 'project_id');
    }

    /**
     * Get the phases for the project.
     */
    public function phases()
    {
        return $this->hasMany(Phase::class);
    }

    /**
     * Get the tasks for the project.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the documents for the project.
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Calculate progress percentage based on active phases.
     * Returns average of phase progress (only active phases).
     */
    public function getProgressPercentAttribute(): int
    {
        $activePhases = $this->phases()->where('status', 'active')->get();
        
        if ($activePhases->isEmpty()) {
            return 0;
        }
        
        $sum = $activePhases->sum(fn($phase) => $phase->progress_percent);
        
        return (int) round($sum / $activePhases->count());
    }
}


