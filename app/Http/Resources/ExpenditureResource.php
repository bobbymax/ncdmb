<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenditureResource extends JsonResource
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
            'owner' => [
                'name' => $this->department->name,
                'abv' => $this->department->abv,
                'department_payment_code' => $this->department->department_payment_code ?? "",
            ],
            'controller' => [
                'name' => "{$this->controller->surname}, {$this->controller->firstname} {$this->controller->middlename}",
                'staff_no' => $this->controller->staff_no,
                'department' => $this->controller->department->abv,
                'role' => $this->controller->role->name
            ],
            'fund' => [
                'department' => $this->fund->department->abv,
                'budget_code' => $this->fund->budgetCode->code,
                'sub_budget_head' => $this->fund->subBudgetHead->name,
                'type' => $this->fund->type,
                'total_approved_amount' => (float) $this->fund->total_approved_amount,
            ]
        ];
    }
}
