<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryIssueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'issued_at' => $this->issued_at,
            'remarks' => $this->remarks,
            'requisition_id' => $this->requisition_id,
            'issued_by' => $this->issued_by,
            'issued_to' => $this->issued_to,
            'from_location_id' => $this->from_location_id,
            'from_location' => new InventoryLocationResource($this->whenLoaded('location')),
            'requisition' => new RequisitionResource($this->whenLoaded('requisition')),
            'items' => InventoryIssueItemResource::collection($this->whenLoaded('items')),
            'transactions' => InventoryTransactionResource::collection($this->whenLoaded('transactions')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
