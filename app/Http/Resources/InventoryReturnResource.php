<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryReturnResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inventory_issue_id' => $this->inventory_issue_id,
            'store_supply_id' => $this->store_supply_id,
            'type' => $this->type,
            'processed_by' => $this->processed_by,
            'location_id' => $this->location_id,
            'returned_at' => $this->returned_at,
            'reason' => $this->reason,
            'meta' => $this->meta,
            'location' => new InventoryLocationResource($this->whenLoaded('location')),
            'issue' => new InventoryIssueResource($this->whenLoaded('issue')),
            'supply' => new StoreSupplyResource($this->whenLoaded('supply')),
            'transactions' => InventoryTransactionResource::collection($this->whenLoaded('transactions')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
