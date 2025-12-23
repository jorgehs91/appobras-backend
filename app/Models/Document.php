<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'project_id',
        'name',
        'file_path',
        'file_url',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    /**
     * Get the company that owns the document.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the project that owns the document.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who uploaded the document.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

