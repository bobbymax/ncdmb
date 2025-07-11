<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SignatoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $department = $this->department_id > 0 ? $this->department->abv : "Originating Dept.";

        return [
            ...parent::toArray($request),
            'group_name' => $this->group->name,
            'page_name' => $this->page->name,
            'department' => $department,
            'compound' => "{$this->page->name} / {$this->group->name} / {$this->type}"
        ];
    }
}
