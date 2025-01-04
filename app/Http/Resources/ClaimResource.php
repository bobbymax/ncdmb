<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClaimResource extends JsonResource
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
            'document' => new DocumentResource($this->document),
            'department_name' => $this->sponsoring_department_id > 0 ? $this->sponsor?->abv : $this->department->abv,
            'expenses' => $this->expenses,
            'uploads' => $this->document->uploads,
            'total_amount_spent' => (float) $this->total_amount_spent,
            'total_amount_approved' => (float) $this->total_amount_approved,
            'total_amount_retired' => (float) $this->total_amount_retired
        ];
    }
}
