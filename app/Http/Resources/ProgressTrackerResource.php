<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgressTrackerResource extends JsonResource
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
            'group' => $this->group,
            'stage' => new WorkflowStageResource($this->stage),
            'workflow' => $this->workflow,
            'actions' => $this->actions,
            'recipients' => $this->recipients
        ];
    }
}
