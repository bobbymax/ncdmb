<?php

namespace App\Http\Resources;

use App\Engine\Puzzle;
use App\Traits\DocumentFlow;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class UploadResource extends JsonResource
{
    use DocumentFlow;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
             'file_path' => $this->asDataUrl()
        ];
    }

    public function asDataUrl(): string
    {
        $cacheKey = "secure_file_{$this->id}";
        return Cache::remember($cacheKey, now()->addMinutes(2), function () {
            $binary = Puzzle::resolve($this->file_path);
            return 'data:' . $this->mime_type . ';base64,' . base64_encode($binary);
        });
    }
}
