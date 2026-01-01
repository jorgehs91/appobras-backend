<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'payable_type' => $this->payable_type,
            'payable_id' => $this->payable_id,
            'amount' => $this->amount,
            'due_date' => $this->due_date?->toDateString(),
            'status' => $this->status?->value,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'payment_proof_path' => $this->payment_proof_path,
            'payable' => $this->whenLoaded('payable', function () {
                if ($this->payable_type === 'App\\Models\\Contract') {
                    return [
                        'id' => $this->payable->id,
                        'contractor_id' => $this->payable->contractor_id,
                        'project_id' => $this->payable->project_id,
                        'value' => $this->payable->value,
                    ];
                } elseif ($this->payable_type === 'App\\Models\\WorkOrder') {
                    return [
                        'id' => $this->payable->id,
                        'contract_id' => $this->payable->contract_id,
                        'description' => $this->payable->description,
                        'value' => $this->payable->value,
                    ];
                }
                return null;
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

