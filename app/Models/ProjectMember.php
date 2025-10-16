<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Project membership pivot with per-project role and preferences.
 */
class ProjectMember extends Pivot
{
    /**
     * @var string
     */
    protected $table = 'project_user';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'user_id',
        'role',
        'joined_at',
        'preferences',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'preferences' => 'array',
        ];
    }
}


