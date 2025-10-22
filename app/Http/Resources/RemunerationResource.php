<?php

namespace App\Http\Resources;

use App\Helpers\Formatter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RemunerationResource extends JsonResource
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
            'allowance' => $this->allowance->name,
            'grade_level' => $this->gradeLevel->key,
//            'amount_formatted' => Formatter::currency($this->amount, $this->currency),
            'expiration_date_formatted' => $this->expiration_date ? Carbon::parse($this->expiration_date)->diffForHumans() : 'N/A',
        ];
    }
}
