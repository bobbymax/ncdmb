<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

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
            'remunerations' => RemunerationResource::collection($this->gradeLevel->remunerations),
            'rank' => $this->ranking(),
            'grade_level_object' => $this->gradeLevel,
            'carder_id' => $this->gradeLevel?->carder_id,
        ];
    }

    protected function ranking()
    {
        $gradeLevelRank = (int) $this->gradeLevel->rank;
        // Get the highest-ranked group for this user
        $highestGroupRank = $this->groups->min('rank') ?? 0;
        return min($gradeLevelRank, $highestGroupRank);
    }
}
