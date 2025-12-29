<?php

namespace App\Models;

use App\Traits\AuditTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CostItem extends Model
{
    use HasFactory, SoftDeletes, AuditTrait;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'budget_id',
        'name',
        'category',
        'planned_amount',
        'unit',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'planned_amount' => 'decimal:2',
        ];
    }

    /**
     * Boot the model and register event listeners.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (CostItem $costItem): void {
            $costItem->validateBudgetTotal();
        });
    }

    /**
     * Validate that the sum of all cost items planned_amount does not exceed budget total_planned.
     *
     * @throws ValidationException
     */
    protected function validateBudgetTotal(): void
    {
        if (! $this->budget_id || ! $this->planned_amount) {
            return;
        }

        $budget = Budget::findOrFail($this->budget_id);
        
        // Calculate current total of all cost items (excluding this one if updating)
        $currentTotal = DB::table('cost_items')
            ->where('budget_id', $this->budget_id)
            ->whereNull('deleted_at')
            ->when($this->exists, function ($query) {
                $query->where('id', '!=', $this->id);
            })
            ->sum('planned_amount');

        $newTotal = $currentTotal + $this->planned_amount;

        if ($newTotal > $budget->total_planned) {
            $exceededAmount = $newTotal - $budget->total_planned;
            throw ValidationException::withMessages([
                'planned_amount' => sprintf(
                    'A soma dos valores planejados dos itens de custo (R$ %s) excede o total planejado do orçamento (R$ %s) em R$ %s.',
                    number_format($newTotal, 2, ',', '.'),
                    number_format($budget->total_planned, 2, ',', '.'),
                    number_format($exceededAmount, 2, ',', '.')
                ),
            ]);
        }
    }

    /**
     * Get the budget that owns the cost item.
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Get the user who created the cost item.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the cost item.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
