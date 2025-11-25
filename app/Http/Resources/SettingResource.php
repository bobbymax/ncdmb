<?php

namespace App\Http\Resources;

use App\Engine\Puzzle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $value = $this->value;
        
        // Only attempt to resolve if input_type is "file" AND value is not null/empty
        if ($this->input_type === "file" && !empty($value)) {
            try {
                $value = Puzzle::resolve($value);
            } catch (\RuntimeException $e) {
                // Log the error but don't break the response
                Log::warning('Failed to resolve Puzzle value for setting', [
                    'setting_id' => $this->id,
                    'setting_key' => $this->key,
                    'error' => $e->getMessage()
                ]);
                // Return null instead of breaking the entire response
                $value = null;
            } catch (\Exception $e) {
                // Catch any other exceptions
                Log::error('Unexpected error resolving Puzzle value', [
                    'setting_id' => $this->id,
                    'setting_key' => $this->key,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $value = null;
            }
        }

        return [
            ...parent::toArray($request),
            'value' => $value
        ];
    }
}
