<?php

namespace App\Models;

use App\Traits\AuditTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contractor extends Model
{
    use HasFactory, SoftDeletes, AuditTrait;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'contact',
        'specialties',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the company that owns the contractor.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the tasks assigned to this contractor.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the user who created the contractor.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the contractor.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

