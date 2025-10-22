<?php

namespace App\Http\Resources;

use App\Helpers\Formatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FundResource extends JsonResource
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
            'budget_code' => $this->budgetCode->code,
            'sub_budget_head' => $this->subBudgetHead->name,
            'name' => "{$this->budgetCode->code} - {$this->subBudgetHead->name}",
            'owner' => $this->department->abv,
//            'approved_amount' => Formatter::currency($this->total_approved_amount),
            'total_commited_amount' => (float) $this->expenditures->sum('amount'),
            'total_actual_amount' => (float) $this->expenditures->where('status', 'posted')->sum('amount'),
            'exhausted' => $this->is_exhausted == 1 ? "Yes" : "No",
        ];
    }
}
