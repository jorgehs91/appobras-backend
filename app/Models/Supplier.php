<?php

namespace App\Models;

use App\Traits\AuditTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, AuditTrait;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'cnpj',
        'contact',
        'created_by',
        'updated_by',
    ];

    /**
     * Boot the model and register event listeners.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Supplier $supplier): void {
            $supplier->validateCnpj();
        });
    }

    /**
     * Validate CNPJ format and uniqueness.
     *
     * @throws ValidationException
     */
    protected function validateCnpj(): void
    {
        if (! $this->cnpj) {
            return;
        }

        // Remove non-numeric characters
        $cnpjNumbers = preg_replace('/\D/', '', $this->cnpj);

        // Validate length (14 digits)
        if (strlen($cnpjNumbers) !== 14) {
            throw ValidationException::withMessages([
                'cnpj' => 'CNPJ deve conter 14 dígitos.',
            ]);
        }

        // Format CNPJ: XX.XXX.XXX/XXXX-XX
        $formattedCnpj = substr($cnpjNumbers, 0, 2) . '.' . substr($cnpjNumbers, 2, 3) . '.' . substr($cnpjNumbers, 5, 3) . '/' . substr($cnpjNumbers, 8, 4) . '-' . substr($cnpjNumbers, 12, 2);

        // Check uniqueness using formatted CNPJ (excluding current record)
        $query = static::where('cnpj', $formattedCnpj);
        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'cnpj' => 'Este CNPJ já está cadastrado.',
            ]);
        }

        // Set formatted CNPJ
        $this->cnpj = $formattedCnpj;
    }

    /**
     * Get the user who created the supplier.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the supplier.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the purchase requests for this supplier.
     */
    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class);
    }
}
