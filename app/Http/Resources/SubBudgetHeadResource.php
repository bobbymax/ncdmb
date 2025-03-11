<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubBudgetHeadResource extends JsonResource
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
            'budget_head_name' => $this->budgetHead->name,
            'fund' => new FundResource($this->fund),
            'has_fund' => $this->fund !== null,
//            'trying' => $this->fund
        ];
    }
}
