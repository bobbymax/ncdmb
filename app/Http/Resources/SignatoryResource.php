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
        return [
            ...parent::toArray($request),
            'group_name' => $this->group->name,
            'page_name' => $this->page->name,
            'department' => $this->department_id > 0 ? $this->department->abv : "Originating Department"
        ];
    }
}
