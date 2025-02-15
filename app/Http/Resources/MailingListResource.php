<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MailingListResource extends JsonResource
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
            'department_name' => $this->department_id < 1 ? 'Originating Department' : $this->department->name,
            'group_name' => $this->group_id > 0 ? $this->group?->name : 'Owner',
            'group' => [
                'id' => $this->group_id,
                'name' => $this->group_id > 0 ? $this->group?->name : 'Owner',
            ],
            'department' => [
                'id' => $this->department_id,
                'name' => $this->department_id < 1 ? 'Originating Department' : $this->department->name,
                'abv' => $this->department_id < 1 ? 'ODD' : $this->department->abv,
            ],
        ];
    }
}
