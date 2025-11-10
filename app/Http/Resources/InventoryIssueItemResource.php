<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryIssueItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inventory_issue_id' => $this->inventory_issue_id,
            'requisition_item_id' => $this->requisition_item_id,
            'product_id' => $this->product_id,
            'product_measurement_id' => $this->product_measurement_id,
            'quantity_issued' => $this->quantity_issued,
            'unit_cost' => $this->unit_cost,
            'batch_id' => $this->batch_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'measurement' => new ProductMeasurementResource($this->whenLoaded('measurement')),
            'batch' => new InventoryBatchResource($this->whenLoaded('batch')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
