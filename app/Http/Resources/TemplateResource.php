<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TemplateResource extends JsonResource
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
            'blocks' => BlockResource::collection($this->documentCategory->blocks),
            'config' => json_decode($this->config),
            'body' => json_decode($this->content, true),
            'content' => null,
            'add_dates' => $this->with_dates ? 'Yes' : 'No'
            // 'category' => new DocumentCategoryResource($this->documentCategory),
        ];
    }
}
