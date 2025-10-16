<?php

namespace App\Models;

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
}


