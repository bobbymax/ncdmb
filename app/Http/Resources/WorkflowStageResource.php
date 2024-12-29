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
            'workflow_name' => $this->workflow->name,
            'group_name' => $this->group->name,
            'actions' => $this->actions,
            'documentsRequired' => $this->requirements,
            'recipients' => $this->recipients
        ];
    }
}
