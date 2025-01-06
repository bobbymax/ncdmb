<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowStageResource extends JsonResource
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
            'group_name' => $this->group->name,
            'support_group_name' => $this->assistant_group_id > 0 ? $this->supportGroup?->name : "",
            'actions' => $this->actions,
            'documentsRequired' => $this->requirements,
            'recipients' => $this->recipients,
//            'docType' => new DocumentTypeResource($this->docType)
        ];
    }
}
