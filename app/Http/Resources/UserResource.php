<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'groups' => $this->groups,
            'name' => "{$this->surname}, {$this->firstname} {$this->middlename}",
            'grade_level' => $this->gradeLevel->key,
            'remunerations' => RemunerationResource::collection($this->gradeLevel->remunerations)
        ];
    }
}
