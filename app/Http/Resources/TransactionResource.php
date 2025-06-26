<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'initiator' => [
                'id' => $this->initiator->id,
                'name' => "{$this->initiator->firstname} {$this->initiator->surname}",
                'staff_no' => $this->initiator->staff_no,
                'department' => $this->initiator->department->abv
            ],
            'journal_type' => new JournalTypeResource($this->journalType)
        ];
    }
}
