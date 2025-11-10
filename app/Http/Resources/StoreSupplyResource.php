<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreSupplyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'department_id' => $this->department_id,
            'project_contract_id' => $this->project_contract_id,
            'product_id' => $this->product_id,
            'product_measurement_id' => $this->product_measurement_id,
            'inventory_location_id' => $this->inventory_location_id,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'delivery_date' => $this->delivery_date,
            'expiration_date' => $this->expiration_date,
            'period_published' => $this->period_published,
            'status' => $this->status,
            'delivery_reference' => $this->delivery_reference,
            'received_at' => $this->received_at,
            'inspection_meta' => $this->inspection_meta,
            'product' => new ProductResource($this->whenLoaded('product')),
            'measurement' => new ProductMeasurementResource($this->whenLoaded('productMeasurement')),
            'location' => new InventoryLocationResource($this->whenLoaded('inventoryLocation')),
            'batches' => InventoryBatchResource::collection($this->whenLoaded('batches')),
            'transactions' => InventoryTransactionResource::collection($this->whenLoaded('transactions')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
