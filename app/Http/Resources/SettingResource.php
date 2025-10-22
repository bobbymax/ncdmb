<?php

namespace App\Http\Resources;

use App\Engine\Puzzle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
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
            'value' => $this->input_type === "file" ? Puzzle::resolve($this->value) : $this->value
        ];
    }
}
