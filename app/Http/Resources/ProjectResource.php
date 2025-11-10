<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'milestones' => MilestoneResource::collection($this->milestones),
            'invoice' => new InvoiceResource($this->invoice),
            'department_owner' => [
                'value' => $this->department->id,
                'label' => $this->department->abv
            ],
            'fund' => [
                'value' => $this->fund->id,
                'label' => "{$this->fund->budgetCode->code} - {$this->fund->subBudgetHead->name}"
            ]
        ];
    }
}
