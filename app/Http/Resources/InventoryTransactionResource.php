<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_measurement_id' => $this->product_measurement_id,
            'location_id' => $this->location_id,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'unit_cost' => $this->unit_cost,
            'value' => $this->value,
            'project_contract_id' => $this->project_contract_id,
            'store_supply_id' => $this->store_supply_id,
            'inventory_issue_id' => $this->inventory_issue_id,
            'inventory_return_id' => $this->inventory_return_id,
            'inventory_adjustment_id' => $this->inventory_adjustment_id,
            'performed_by' => $this->performed_by,
            'transacted_at' => $this->transacted_at,
            'meta' => $this->meta,
            'product' => new ProductResource($this->whenLoaded('product')),
            'location' => new InventoryLocationResource($this->whenLoaded('location')),
            'measurement' => new ProductMeasurementResource($this->whenLoaded('measurement')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
