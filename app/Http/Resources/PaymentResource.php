<?php

namespace App\Http\Resources;

use App\Models\Document;
use App\Models\DocumentDraft;
use App\Traits\ResourceContainer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'expenditure' => new ExpenditureResource($this->expenditure),
            'batch' => [
                'department' => $this->batch->department->abv,
                'budget_code' => $this->batch->fund->budgetCode->code,
                'code' => $this->batch->code,
                'account_code' => $this->chart_of_account_id > 0 ? $this->chartOfAccount->code : "",
            ],
            'fiscal_year' => $this->budget_year,
            'transactions' => TransactionResource::collection($this->transactions),
            'books' => JournalTypeResource::collection($this->journalTypes),
            'journal' => $this->journal ? new JournalResource($this->journal) : null,
        ];
    }
}
