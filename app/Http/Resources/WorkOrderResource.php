<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkOrderResource extends JsonResource
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
            'contract_id' => $this->contract_id,
            'description' => $this->description,
            'value' => $this->value,
            'due_date' => $this->due_date?->toDateString(),
            'status' => $this->status?->value,
            'contract' => $this->whenLoaded('contract', function () {
                return [
                    'id' => $this->contract->id,
                    'contractor_id' => $this->contract->contractor_id,
                    'project_id' => $this->contract->project_id,
                ];
            }),
            'payments_count' => $this->whenCounted('payments'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

