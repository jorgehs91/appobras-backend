<?php

namespace App\Models;

use App\Traits\AuditTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class License extends Model
{
    use HasFactory, SoftDeletes, AuditTrait;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'file_id',
        'project_id',
        'expiry_date',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
        ];
    }

    /**
     * Get the file associated with this license.
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the project that owns this license.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who created this license.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this license.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope a query to only include licenses for a specific project.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $projectId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope a query to filter licenses by status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include licenses that are expiring soon.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>=', now());
    }

    /**
     * Scope a query to only include expired licenses.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    /**
     * Scope a query to only include active (non-expired) licenses.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('expiry_date', '>=', now());
    }

    /**
     * Check if the license is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expiry_date < now();
    }

    /**
     * Check if the license is expiring soon.
     *
     * @param  int  $days
     * @return bool
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date <= now()->addDays($days) && $this->expiry_date >= now();
    }

    /**
     * Get the number of days until expiration.
     *
     * @return int
     */
    public function daysUntilExpiration(): int
    {
        return max(0, now()->diffInDays($this->expiry_date, false));
    }
}
