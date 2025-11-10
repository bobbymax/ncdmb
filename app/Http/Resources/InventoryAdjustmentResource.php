<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryAdjustmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'location_id' => $this->location_id,
            'performed_by' => $this->performed_by,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'adjusted_at' => $this->adjusted_at,
            'meta' => $this->meta,
            'location' => new InventoryLocationResource($this->whenLoaded('location')),
            'performed_by_user' => new UserResource($this->whenLoaded('performedBy')),
            'transactions' => InventoryTransactionResource::collection($this->whenLoaded('transactions')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
