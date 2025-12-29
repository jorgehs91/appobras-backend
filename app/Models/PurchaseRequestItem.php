<?php

namespace App\Models;

use App\Traits\AuditTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class PurchaseRequestItem extends Model
{
    use HasFactory, SoftDeletes, AuditTrait;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'purchase_request_id',
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

        static::saving(function (PurchaseRequestItem $item): void {
            $item->validateFields();
            $item->calculateTotal();
        });

        static::saved(function (PurchaseRequestItem $item): void {
            $item->purchaseRequest->calculateTotal();
            $item->purchaseRequest->save();
        });

        static::deleted(function (PurchaseRequestItem $item): void {
            $item->purchaseRequest->calculateTotal();
            $item->purchaseRequest->save();
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
     * Get the purchase request that owns this item.
     */
    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    /**
     * Get the cost item associated with this purchase request item.
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
