<?php

namespace App\Models;

use App\Events\ApprovedPurchaseRequest;
use App\Enums\PurchaseRequestStatus;
use App\Jobs\GeneratePurchaseOrder;
use App\Traits\AuditTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class PurchaseRequest extends Model
{
    use HasFactory, SoftDeletes, AuditTrait;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'supplier_id',
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
            'status' => PurchaseRequestStatus::class,
            'total' => 'decimal:2',
        ];
    }

    /**
     * Boot the model and register event listeners.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (PurchaseRequest $purchaseRequest): void {
            $purchaseRequest->validateStatus();
            $purchaseRequest->validateStatusTransition();
            $purchaseRequest->calculateTotal();
        });

        static::updating(function (PurchaseRequest $purchaseRequest): void {
            // Prevent editing approved purchase requests (except status changes)
            if ($purchaseRequest->isDirty() && ! $purchaseRequest->isDirty('status')) {
                $original = $purchaseRequest->getOriginal();
                if (isset($original['status'])) {
                    // Handle both enum instance and string value
                    $originalStatusValue = $original['status'];
                    $originalStatus = $originalStatusValue instanceof PurchaseRequestStatus
                        ? $originalStatusValue
                        : PurchaseRequestStatus::from($originalStatusValue);

                    if ($originalStatus === PurchaseRequestStatus::approved) {
                        throw ValidationException::withMessages([
                            'status' => 'Não é possível editar uma requisição de compra aprovada.',
                        ]);
                    }
                }
            }
        });

        static::updated(function (PurchaseRequest $purchaseRequest): void {
            // Dispatch event when PR is approved
            if ($purchaseRequest->isDirty('status') && $purchaseRequest->status === PurchaseRequestStatus::approved) {
                event(new ApprovedPurchaseRequest($purchaseRequest));
                GeneratePurchaseOrder::dispatch($purchaseRequest);
            }
        });
    }

    /**
     * Validate status enum value.
     *
     * @throws ValidationException
     */
    protected function validateStatus(): void
    {
        // If status is already an enum instance, it's valid
        if ($this->status instanceof PurchaseRequestStatus) {
            return;
        }

        // If status is a string, try to convert it
        if (is_string($this->status)) {
            try {
                $this->status = PurchaseRequestStatus::from($this->status);
            } catch (\ValueError $e) {
                throw ValidationException::withMessages([
                    'status' => 'Status inválido. Valores permitidos: draft, submitted, approved, rejected.',
                ]);
            }
        }
    }

    /**
     * Validate status transition according to workflow rules.
     *
     * @throws ValidationException
     */
    protected function validateStatusTransition(): void
    {
        // Skip validation for new records
        if (! $this->exists || ! $this->isDirty('status')) {
            return;
        }

        $original = $this->getOriginal();
        if (! isset($original['status'])) {
            return;
        }

        // Handle both enum instance and string value
        $oldStatusValue = $original['status'];
        if ($oldStatusValue instanceof PurchaseRequestStatus) {
            $oldStatus = $oldStatusValue;
        } else {
            $oldStatus = PurchaseRequestStatus::from($oldStatusValue);
        }

        $newStatus = $this->status;

        // Define allowed transitions
        $allowedTransitions = [
            PurchaseRequestStatus::draft->value => [
                PurchaseRequestStatus::submitted->value,
            ],
            PurchaseRequestStatus::submitted->value => [
                PurchaseRequestStatus::approved->value,
                PurchaseRequestStatus::rejected->value,
                PurchaseRequestStatus::draft->value, // Allow going back to draft
            ],
            PurchaseRequestStatus::rejected->value => [
                PurchaseRequestStatus::draft->value, // Allow editing and resubmitting
            ],
            PurchaseRequestStatus::approved->value => [
                // Once approved, cannot change status
            ],
        ];

        $allowed = $allowedTransitions[$oldStatus->value] ?? [];

        if (! in_array($newStatus->value, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => sprintf(
                    'Transição de status inválida: não é possível alterar de "%s" para "%s".',
                    $oldStatus->value,
                    $newStatus->value
                ),
            ]);
        }
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
     * Get the project that owns the purchase request.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the supplier for this purchase request.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the items for this purchase request.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    /**
     * Get the purchase order generated from this purchase request.
     */
    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class);
    }

    /**
     * Get the user who created the purchase request.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the purchase request.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if the purchase request can be edited.
     */
    public function canBeEdited(): bool
    {
        return $this->status === PurchaseRequestStatus::draft || $this->status === PurchaseRequestStatus::rejected;
    }

    /**
     * Check if the purchase request can be deleted.
     */
    public function canBeDeleted(): bool
    {
        return $this->status === PurchaseRequestStatus::draft;
    }
}
