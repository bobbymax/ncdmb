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
            'grade_level' => $this->gradeLevel->key,
            'grade_level_id' => $this->gradeLevel->id,
            'staff_no' => $this->staff_no,
            'role_name' => $this->role->name,
            'role_label' => $this->role->slug,
            'pages' => PageResource::collection($pages),
            'default_page_id' => $this->default_page_id,
            'remunerations' => $this->gradeLevel->remunerations()->where('is_active', 1)->latest()->get(),
        ];
    }
}
