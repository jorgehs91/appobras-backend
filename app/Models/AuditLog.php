<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'auditable_id',
        'auditable_type',
        'event',
        'old_values',
        'new_values',
        'ip',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent auditable model (polymorphic).
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to filter by project.
     * 
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $projectId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByProject($query, int $projectId)
    {
        return $query->where(function ($q) use ($projectId) {
            // Direct project relationship
            $q->where(function ($subQ) use ($projectId) {
                $subQ->where('auditable_type', Project::class)
                    ->where('auditable_id', $projectId);
            })
            // Phase belongs to project - using subquery
            ->orWhere(function ($subQ) use ($projectId) {
                $subQ->where('auditable_type', Phase::class)
                    ->whereIn('auditable_id', function ($phaseQuery) use ($projectId) {
                        $phaseQuery->select('id')
                            ->from('phases')
                            ->where('project_id', $projectId);
                    });
            })
            // Task belongs to project - using subquery
            ->orWhere(function ($subQ) use ($projectId) {
                $subQ->where('auditable_type', Task::class)
                    ->whereIn('auditable_id', function ($taskQuery) use ($projectId) {
                        $taskQuery->select('id')
                            ->from('tasks')
                            ->where('project_id', $projectId);
                    });
            })
            // File belongs to project (polymorphic) - using subquery
            ->orWhere(function ($subQ) use ($projectId) {
                $subQ->where('auditable_type', File::class)
                    ->whereIn('auditable_id', function ($fileQuery) use ($projectId) {
                        $fileQuery->select('id')
                            ->from('files')
                            ->where('project_id', $projectId);
                    });
            });
        });
    }

    /**
     * Scope a query to filter by company.
     * 
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $companyId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCompany($query, int $companyId)
    {
        return $query->where(function ($q) use ($companyId) {
            // Direct company relationship
            $q->where(function ($subQ) use ($companyId) {
                $subQ->where('auditable_type', Company::class)
                    ->where('auditable_id', $companyId);
            })
            // Project belongs to company - using subquery
            ->orWhere(function ($subQ) use ($companyId) {
                $subQ->where('auditable_type', Project::class)
                    ->whereIn('auditable_id', function ($projectQuery) use ($companyId) {
                        $projectQuery->select('id')
                            ->from('projects')
                            ->where('company_id', $companyId);
                    });
            })
            // Phase belongs to company - using subquery
            ->orWhere(function ($subQ) use ($companyId) {
                $subQ->where('auditable_type', Phase::class)
                    ->whereIn('auditable_id', function ($phaseQuery) use ($companyId) {
                        $phaseQuery->select('id')
                            ->from('phases')
                            ->where('company_id', $companyId);
                    });
            })
            // Contractor belongs to company - using subquery
            ->orWhere(function ($subQ) use ($companyId) {
                $subQ->where('auditable_type', Contractor::class)
                    ->whereIn('auditable_id', function ($contractorQuery) use ($companyId) {
                        $contractorQuery->select('id')
                            ->from('contractors')
                            ->where('company_id', $companyId);
                    });
            })
            // Task belongs to company - using subquery
            ->orWhere(function ($subQ) use ($companyId) {
                $subQ->where('auditable_type', Task::class)
                    ->whereIn('auditable_id', function ($taskQuery) use ($companyId) {
                        $taskQuery->select('id')
                            ->from('tasks')
                            ->where('company_id', $companyId);
                    });
            })
            // File belongs to company (polymorphic) - using subquery
            ->orWhere(function ($subQ) use ($companyId) {
                $subQ->where('auditable_type', File::class)
                    ->whereIn('auditable_id', function ($fileQuery) use ($companyId) {
                        $fileQuery->select('id')
                            ->from('files')
                            ->where('company_id', $companyId);
                    });
            });
        });
    }
}
