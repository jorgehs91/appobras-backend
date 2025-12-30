<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
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
            'cost_item_id' => $this->cost_item_id,
            'project_id' => $this->project_id,
            'amount' => (float) $this->amount,
            'date' => $this->date?->format('Y-m-d'),
            'description' => $this->description,
            'receipt_path' => $this->receipt_path,
            'status' => $this->status->value,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'cost_item' => $this->whenLoaded('costItem', fn () => new CostItemResource($this->costItem)),
            'project' => $this->whenLoaded('project', fn () => new ProjectResource($this->project)),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}

