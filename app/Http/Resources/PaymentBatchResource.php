<?php

namespace App\Http\Resources;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentBatchResource extends JsonResource
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
            'expenditures' => ExpenditureResource::collection($this->document->expenditures),
            'directorate' => $this->placement($this->document->ref, 1),
            'department' => $this->placement($this->document->ref, 2),
        ];
    }

    protected function placement($ref, int $index)
    {
        $abvParts = array_slice(explode('/', $ref), 0, 2);
        $department = $this->getDepartment($abvParts[$index-1]);

        if (!$department) {
            return "";
        }

        return $department->name;
    }

    protected function getDepartment($abv)
    {
        return Department::where('abv', $abv)->firstOrFail();
    }
}
