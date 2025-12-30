<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestResource extends JsonResource
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
            'supplier_id' => $this->supplier_id,
            'status' => $this->status->value,
            'total' => (string) $this->total,
            'notes' => $this->notes,
            'items' => PurchaseRequestItemResource::collection($this->whenLoaded('items')),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'purchase_order' => new PurchaseOrderResource($this->whenLoaded('purchaseOrder')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

