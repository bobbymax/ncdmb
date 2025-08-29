<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $pages = $this->role->pages()->where('is_disabled', 0)->latest()->get();
        return [
            'id' => $this->id,
            'name' => "{$this->firstname} {$this->surname}",
            'department_id' => $this->department_id,
            'department' => [
                'value' => $this->department_id,
                'label' => $this->department->abv
            ],
            'grade_level_object' => $this->gradeLevel,
            'grade_level' => $this->gradeLevel->key,
            'grade_level_id' => $this->gradeLevel->id,
            'carder' => $this->gradeLevel->carder,
            'staff_no' => $this->staff_no,
            'role' => $this->role,
            'role_name' => $this->role->name,
            'role_label' => $this->role->slug,
            'pages' => PageResource::collection($pages),
            'groups' => GroupResource::collection($this->groups),
            'default_page_id' => $this->default_page_id,
            'remunerations' => $this->gradeLevel->remunerations()->where('is_active', 1)->latest()->get(),
        ];
    }
}
