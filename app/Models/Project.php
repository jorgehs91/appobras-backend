<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Traits\AuditTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory, AuditTrait;

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
        'created_by',
        'updated_by',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created the project.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the project.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
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
    /**
     * Get all files for this project (polymorphic).
     */
    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * Get documents for this project (alias for files with category='document').
     * 
     * @deprecated Use files()->where('category', 'document') instead
     */
    public function documents()
    {
        return $this->files()->where('category', 'document');
    }

    /**
     * Get the budget for the project.
     */
    public function budget()
    {
        return $this->hasOne(Budget::class);
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


