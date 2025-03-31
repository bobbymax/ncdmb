<?php

namespace App\Http\Resources;

use App\Engine\Puzzle;
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
            'image_path' => $this->image_path ? Puzzle::resolve($this->image_path) : null,
            'parent' => $this->parent_id > 0 ? $this->parent->name : "None",
            'roles' => $this->roles,
            'workflow' => $this->workflow_id > 0 ? new WorkflowResource($this->workflow) : null,
            'workflow_name' => $this->workflow_id > 0 ? $this->workflow?->name : "None",
            'documentType' => $this->document_type_id > 0 ? new DocumentTypeResource($this->documentType) : null,
            'signatories' => SignatoryResource::collection($this->signatories)
        ];
    }
}
