<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /** @var \App\Models\Project */
    public $resource;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status?->value,
            'archived_at' => optional($this->archived_at)?->toISOString(),
            'start_date' => optional($this->start_date)?->toDateString(),
            'end_date' => optional($this->end_date)?->toDateString(),
            'actual_start_date' => optional($this->actual_start_date)?->toDateString(),
            'actual_end_date' => optional($this->actual_end_date)?->toDateString(),
            'planned_budget_amount' => $this->planned_budget_amount !== null ? (string) $this->planned_budget_amount : null,
            'manager_user_id' => $this->manager_user_id,
            'address' => $this->address,
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}


