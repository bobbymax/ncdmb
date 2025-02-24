<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'department_name' => $this->department_id < 1 ? 'Originating Department' : $this->department?->abv,
            'stage_category' => new WorkflowStageCategoryResource($this->workflowStageCategory),
            'groups' => GroupResource::collection($this->groups),
            'actions' => DocumentActionResource::collection($this->actions),
            'fallback' => $this->fallback,
            'recipients' => $this->recipients
        ];
    }
}
