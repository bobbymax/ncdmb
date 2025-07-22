<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
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
            'carderIds' => $this->carders->pluck('id')->toArray(),
            // This is the key part
            'users' => $this->users->map(function ($user) {
                return [
                    'value' => $user->id,
                    'label' => "{$user->surname}, {$user->firstname}", // or $user->full_name or $user->username
                ];
            })->values(),
        ];
    }
}
