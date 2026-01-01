<?php

namespace App\Models;

use App\Enums\ContractStatus;
use App\Traits\AuditTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory, SoftDeletes, AuditTrait;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'contractor_id',
        'project_id',
        'value',
        'start_date',
        'end_date',
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
            'value' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => ContractStatus::class,
        ];
    }

    /**
     * Get the contractor that owns the contract.
     */
    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    /**
     * Get the project that owns the contract.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the work orders for the contract.
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    /**
     * Get the user who created the contract.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the contract.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
