<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
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
            'task_id' => $this->fileable_id,
            'filename' => $this->name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'thumbnail_path' => $this->thumbnail_path,
            'user_id' => $this->uploaded_by,
            'user_name' => $this->whenLoaded('uploader', fn() => $this->uploader?->name),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
