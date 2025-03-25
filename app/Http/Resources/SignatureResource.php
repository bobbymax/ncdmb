<?php

namespace App\Http\Resources;

use App\Engine\Puzzle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

class SignatureResource extends JsonResource
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
            'type' => $this->signatory->type,
            'approving_officer' => [
                'name' => "{$this->staff->surname}, {$this->staff->firstname} {$this->staff->middlename}",
                'grade_level' => $this->staff->gradeLevel->key,
            ],
            'signature' => Puzzle::resolve($this->signature)
        ];
    }
}
