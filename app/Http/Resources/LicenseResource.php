<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="License",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="file_id", type="integer", example=1),
 *     @OA\Property(property="project_id", type="integer", example=1),
 *     @OA\Property(property="expiry_date", type="string", format="date", example="2025-12-31"),
 *     @OA\Property(property="status", type="string", nullable=true, example="active"),
 *     @OA\Property(property="notes", type="string", nullable=true, example="Licença de alvará de construção"),
 *     @OA\Property(property="is_expired", type="boolean", example=false),
 *     @OA\Property(property="is_expiring_soon", type="boolean", example=false),
 *     @OA\Property(property="days_until_expiration", type="integer", example=30),
 *     @OA\Property(
 *         property="file",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="license.pdf"),
 *         @OA\Property(property="path", type="string", example="files/license.pdf"),
 *         @OA\Property(property="mime_type", type="string", example="application/pdf"),
 *         @OA\Property(property="size", type="integer", example=1024)
 *     ),
 *     @OA\Property(
 *         property="project",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Projeto ABC")
 *     ),
 *     @OA\Property(
 *         property="creator",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="João Silva"),
 *         @OA\Property(property="email", type="string", example="joao@example.com")
 *     ),
 *     @OA\Property(
 *         property="updater",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="João Silva"),
 *         @OA\Property(property="email", type="string", example="joao@example.com")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-01T12:00:00Z")
 * )
 */
class LicenseResource extends JsonResource
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
            'file_id' => $this->file_id,
            'project_id' => $this->project_id,
            'expiry_date' => $this->expiry_date?->toDateString(),
            'status' => $this->status,
            'notes' => $this->notes,
            'is_expired' => $this->isExpired(),
            'is_expiring_soon' => $this->isExpiringSoon(),
            'days_until_expiration' => $this->daysUntilExpiration(),
            'file' => $this->whenLoaded('file', function () {
                return [
                    'id' => $this->file->id,
                    'name' => $this->file->name,
                    'path' => $this->file->path,
                    'mime_type' => $this->file->mime_type,
                    'size' => $this->file->size,
                ];
            }),
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'name' => $this->project->name,
                ];
            }),
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),
            'updater' => $this->whenLoaded('updater', function () {
                return [
                    'id' => $this->updater->id,
                    'name' => $this->updater->name,
                    'email' => $this->updater->email,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

