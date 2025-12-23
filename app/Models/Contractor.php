<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contractor extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'contact',
        'specialties',
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
}

