<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskCommentResource extends JsonResource
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
            'task_id' => $this->task_id,
            'user_id' => $this->user_id,
            'body' => $this->body,
            'reactions' => $this->reactions,
            'user' => [
                'id' => $this->whenLoaded('user', fn() => $this->user->id),
                'name' => $this->whenLoaded('user', fn() => $this->user->name),
                'email' => $this->whenLoaded('user', fn() => $this->user->email),
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
