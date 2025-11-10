<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryBalanceResource extends JsonResource
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
            'on_hand' => $this->on_hand,
            'reserved' => $this->reserved,
            'available' => $this->available,
            'unit_cost' => $this->unit_cost,
            'last_movement_at' => $this->last_movement_at,
            'product' => new ProductResource($this->whenLoaded('product')),
            'measurement' => new ProductMeasurementResource($this->whenLoaded('measurement')),
            'location' => new InventoryLocationResource($this->whenLoaded('location')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
