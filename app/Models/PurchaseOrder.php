<?php

namespace App\Models;

use App\Enums\PurchaseOrderStatus;
use App\Traits\AuditTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, AuditTrait;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'purchase_request_id',
        'po_number',
        'status',
        'total',
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
            'status' => PurchaseOrderStatus::class,
            'total' => 'decimal:2',
        ];
    }

    /**
     * Boot the model and register event listeners.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (PurchaseOrder $purchaseOrder): void {
            if (empty($purchaseOrder->po_number)) {
                $purchaseOrder->po_number = $purchaseOrder->generatePoNumber();
            }
        });

        static::saving(function (PurchaseOrder $purchaseOrder): void {
            $purchaseOrder->calculateTotal();
        });
    }

    /**
     * Generate a unique PO number in format PO-YYYYMM-####.
     *
     * @return string
     */
    protected function generatePoNumber(): string
    {
        $prefix = 'PO-' . now()->format('Ym') . '-';
        
        return DB::transaction(function () use ($prefix) {
            // Get the last PO number for this month
            $lastPo = self::where('po_number', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            if ($lastPo) {
                // Extract the sequence number and increment
                $lastSequence = (int) substr($lastPo->po_number, -4);
                $sequence = $lastSequence + 1;
            } else {
                // First PO of the month
                $sequence = 1;
            }

            // Format with leading zeros (4 digits)
            $poNumber = $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

            // Double-check uniqueness (in case of race condition)
            $exists = self::where('po_number', $poNumber)->exists();
            if ($exists) {
                // If exists, try next number
                $sequence++;
                $poNumber = $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            }

            return $poNumber;
        });
    }

    /**
     * Calculate total from items.
     */
    public function calculateTotal(): void
    {
        if ($this->relationLoaded('items')) {
            $this->total = $this->items->sum('total');
        } else {
            $this->total = $this->items()->sum('total');
        }
    }

    /**
     * Get the purchase request that owns this purchase order.
     */
    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    /**
     * Get the items for this purchase order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get the user who created the purchase order.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the purchase order.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope to filter by project.
     */
    public function scopeForProject($query, int $projectId)
    {
        return $query->whereHas('purchaseRequest', function ($q) use ($projectId) {
            $q->where('project_id', $projectId);
        });
    }
}
