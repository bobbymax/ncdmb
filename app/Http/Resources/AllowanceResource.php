<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllowanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'parent' => $this->parent_id > 0 ? $this->parent?->name : 'None',
            'status' => $this->days_required == 1 ? 'Yes' : 'No',
            'active' => $this->is_active == 1 ? 'Yes' : 'No',
        ];
    }
}
