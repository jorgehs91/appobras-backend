<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CostItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'budget_id' => $this->budget_id,
            'name' => $this->name,
            'category' => $this->category,
            'planned_amount' => (float) $this->planned_amount,
            'unit' => $this->unit,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'budget' => new BudgetResource($this->whenLoaded('budget')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}