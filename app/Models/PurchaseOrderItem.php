<?php

namespace App\Models;

use App\Traits\AuditTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class PurchaseOrderItem extends Model
{
    use HasFactory, SoftDeletes, AuditTrait;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'purchase_order_id',
        'purchase_request_item_id',
        'cost_item_id',
        'description',
        'quantity',
        'unit_price',
        'total',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /**
     * Boot the model and register event listeners.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (PurchaseOrderItem $item): void {
            $item->validateFields();
            $item->calculateTotal();
        });

        static::saved(function (PurchaseOrderItem $item): void {
            $item->purchaseOrder->calculateTotal();
            $item->purchaseOrder->save();
        });

        static::deleted(function (PurchaseOrderItem $item): void {
            $item->purchaseOrder->calculateTotal();
            $item->purchaseOrder->save();
        });
    }

    /**
     * Validate item fields.
     *
     * @throws ValidationException
     */
    protected function validateFields(): void
    {
        if ($this->quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'A quantidade deve ser maior que zero.',
            ]);
        }

        if ($this->unit_price < 0) {
            throw ValidationException::withMessages([
                'unit_price' => 'O preço unitário não pode ser negativo.',
            ]);
        }
    }

    /**
     * Calculate total (quantity * unit_price).
     */
    protected function calculateTotal(): void
    {
        $this->total = $this->quantity * $this->unit_price;
    }

    /**
     * Get the purchase order that owns this item.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the purchase request item that originated this order item.
     */
    public function purchaseRequestItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequestItem::class);
    }

    /**
     * Get the cost item associated with this purchase order item.
     */
    public function costItem(): BelongsTo
    {
        return $this->belongsTo(CostItem::class);
    }

    /**
     * Get the user who created the item.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the item.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
