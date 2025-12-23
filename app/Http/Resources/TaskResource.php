<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'phase_id' => $this->phase_id,
            'phase_name' => $this->whenLoaded('phase', fn() => $this->phase->name),
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status?->value,
            'priority' => $this->priority?->value,
            'order_in_phase' => $this->order_in_phase,
            'assignee_id' => $this->assignee_id,
            'assignee_name' => $this->whenLoaded('assignee', fn() => $this->assignee?->name),
            'contractor_id' => $this->contractor_id,
            'contractor_name' => $this->whenLoaded('contractor', fn() => $this->contractor?->name),
            'is_blocked' => $this->is_blocked,
            'blocked_reason' => $this->blocked_reason,
            'planned_start_at' => $this->planned_start_at?->toDateString(),
            'planned_end_at' => $this->planned_end_at?->toDateString(),
            'due_at' => $this->due_at?->toDateString(),
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'progress_percent' => $this->progress_percent,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

