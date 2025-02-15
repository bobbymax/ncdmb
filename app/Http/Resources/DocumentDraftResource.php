<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class DocumentDraftResource extends JsonResource
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
            'template' => $this->getTemplate(),
            'draftable' => $this->resolveDraftableResource()
        ];
    }

    private function getTemplate()
    {
        if ($this->document_type_id < 1) {
            return null;
        }

        if ($this->documentType && $this->documentType->file_template_id < 1) {
            return null;
        }

        return $this->documentType->template;
    }

    private function resolveDraftableResource(): ?JsonResource
    {
        if (!$this->documentDraftable) {
            return null;
        }

        $modelClassName = class_basename($this->documentDraftable);

        $resourceClass = "App\\Http\\Resources\\{$modelClassName}Resource";

        if (class_exists($resourceClass)) {
            return new $resourceClass($this->documentDraftable);
        }

        return null;
    }
}
