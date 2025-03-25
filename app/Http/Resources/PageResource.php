<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
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
            'parent' => $this->parent_id > 0 ? $this->parent->name : "None",
            'roles' => $this->roles,
            'workflow' => $this->workflow_id > 0 ? new WorkflowResource($this->workflow) : null,
            'workflow_name' => $this->workflow_id > 0 ? $this->workflow?->name : "None",
            'documentType' => $this->document_type_id > 0 ? new DocumentTypeResource($this->documentType) : null,
            'signatories' => SignatoryResource::collection($this->signatories)
        ];
    }
}
