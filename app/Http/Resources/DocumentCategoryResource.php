<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentCategoryResource extends JsonResource
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
            'document_type' => $this->documentType?->label,
            'workflow' => new WorkflowResource($this->workflow),
            'requirements' => $this->requirements,
            'blocks' => BlockResource::collection($this->blocks),
            'template' => $this->template ? new TemplateResource($this->template) : null,
            'workflow' => json_decode($this->workflow),
            'config' => json_decode($this->config),
            'content' => json_decode($this->content),
            'meta_data' => json_decode($this->meta_data),
        ];
    }
}
