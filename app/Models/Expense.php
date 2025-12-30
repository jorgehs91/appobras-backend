<?php

namespace App\Models;

use App\Enums\ExpenseStatus;
use App\Traits\AuditTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes, AuditTrait;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'cost_item_id',
        'project_id',
        'amount',
        'date',
        'description',
        'receipt_path',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
            'status' => ExpenseStatus::class,
        ];
    }

    /**
     * Get the cost item that this expense is associated with (nullable).
     */
    public function costItem(): BelongsTo
    {
        return $this->belongsTo(CostItem::class);
    }

    /**
     * Get the project that owns the expense.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who created the expense.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the expense.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope a query to only include expenses for a specific project.
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
     * Scope a query to filter expenses by status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  ExpenseStatus  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, ExpenseStatus $status)
    {
        return $query->where('status', $status->value);
    }

    /**
     * Scope a query to only include expenses with receipts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithReceipt($query)
    {
        return $query->whereNotNull('receipt_path');
    }
}
