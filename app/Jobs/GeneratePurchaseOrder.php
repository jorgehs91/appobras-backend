<?php

namespace App\Jobs;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeneratePurchaseOrder implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public PurchaseRequest $purchaseRequest
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Check if PO already exists for this PR (idempotency)
        $existingPo = PurchaseOrder::where('purchase_request_id', $this->purchaseRequest->id)->first();

        if ($existingPo) {
            Log::info('Purchase Order already exists for Purchase Request', [
                'purchase_request_id' => $this->purchaseRequest->id,
                'purchase_order_id' => $existingPo->id,
                'po_number' => $existingPo->po_number,
            ]);

            return;
        }

        // Verify PR is approved
        $statusValue = $this->purchaseRequest->status instanceof \App\Enums\PurchaseRequestStatus
            ? $this->purchaseRequest->status->value
            : $this->purchaseRequest->status;

        if ($statusValue !== 'approved') {
            Log::warning('Cannot generate PO for non-approved Purchase Request', [
                'purchase_request_id' => $this->purchaseRequest->id,
                'status' => $statusValue,
            ]);

            return;
        }

        DB::transaction(function () {
            // Create Purchase Order
            $purchaseOrder = PurchaseOrder::create([
                'purchase_request_id' => $this->purchaseRequest->id,
                'status' => PurchaseOrderStatus::pending,
                'notes' => $this->purchaseRequest->notes,
            ]);

            // Copy items from Purchase Request Items
            $this->purchaseRequest->load('items');

            foreach ($this->purchaseRequest->items as $requestItem) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'purchase_request_item_id' => $requestItem->id,
                    'cost_item_id' => $requestItem->cost_item_id,
                    'description' => $requestItem->description,
                    'quantity' => $requestItem->quantity,
                    'unit_price' => $requestItem->unit_price,
                ]);
            }

            // Recalculate total
            $purchaseOrder->calculateTotal();
            $purchaseOrder->save();

            Log::info('Purchase Order generated successfully', [
                'purchase_request_id' => $this->purchaseRequest->id,
                'purchase_order_id' => $purchaseOrder->id,
                'po_number' => $purchaseOrder->po_number,
                'total' => $purchaseOrder->total,
            ]);
        });
    }
}
