<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource para membros de projeto (User com dados do pivot)
 */
class ProjectMemberResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->pivot->role ?? null,
            'joined_at' => optional($this->pivot->joined_at ?? null)?->toISOString(),
            'preferences' => $this->pivot->preferences ?? null,
        ];
    }
}
