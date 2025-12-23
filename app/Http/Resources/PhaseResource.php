<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhaseResource extends JsonResource
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
            'project_id' => $this->project_id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status?->value,
            'sequence' => $this->sequence,
            'color' => $this->color,
            'planned_start_at' => $this->planned_start_at?->toDateString(),
            'planned_end_at' => $this->planned_end_at?->toDateString(),
            'actual_start_at' => $this->actual_start_at?->toDateString(),
            'actual_end_at' => $this->actual_end_at?->toDateString(),
            'progress_percent' => $this->progress_percent,
            'tasks_counts' => $this->tasks_counts,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

