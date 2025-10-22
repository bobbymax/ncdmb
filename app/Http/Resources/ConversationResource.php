<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
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
            'user_id' => $this->sender->id,
            'user' => [
                'id' => $this->sender->id,
                'name' => "{$this->sender->surname}, {$this->sender->firstname} {$this->sender->middlename}",
                'email' => $this->sender->email,
            ]
        ];
    }
}
