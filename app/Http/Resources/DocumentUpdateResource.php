<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentUpdateResource extends JsonResource
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
            'threads' => json_decode($this->threads),
            'user' => [
                'name' => "{$this->user->surname}, {$this->user->firstname} {$this->user->middlename}",
                'staff_no' => $this->user->staff_no,
                'department' => $this->user->department->abv,
                'role' => $this->user->role->name,
                'avatar' => null
            ],
            'meta' => [
                'document_type_name' => $this->draft->documentType->name,
                'current_stage' => $this->draft->workflowStage->name,
                'document_action_name' => $this->action->button_text,
            ]
        ];
    }
}
