<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryLocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'department_id' => $this->department_id,
            'parent_id' => $this->parent_id,
            'meta' => $this->meta,
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'parent' => new InventoryLocationResource($this->whenLoaded('parent')),
            'children_count' => $this->whenCounted('children'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
