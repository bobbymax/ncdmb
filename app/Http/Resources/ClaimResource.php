<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ClaimResource extends JsonResource
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
            'department_name' => $this->sponsoring_department_id > 0 ? $this->sponsor?->abv : $this->department->abv,
            'expenses' => $this->expenses,
            'uploads' => UploadResource::collection($this->document->uploads),
            'total_amount_spent' => (float) $this->total_amount_spent,
            'total_amount_approved' => (float) $this->total_amount_approved,
            'total_amount_retired' => (float) $this->total_amount_retired,
            'owner' => [
                'staff_no' => $this->staff?->staff_no ?? "none",
                'grade_level' => $this->staff?->gradeLevel?->key ?? "none",
                'name' => "{$this->staff?->surname}, {$this->staff?->firstname} {$this->staff?->middlename}"
            ],
            'claimant_signature' => $this->getClaimSignature($this->claimant_signature),
            'approval_signature' => $this->getClaimSignature($this->approval_signature)
        ];
    }

    protected function getClaimSignature($path): ?string
    {
        if (empty($path) || !Storage::disk('public')->exists($path)) {
            return null; // Return null for non-existent paths
        }

        $signatureContent = Storage::disk('public')->get($path);
        $mimeType = Storage::disk('public')->mimeType($path);

        // Only support specific MIME types
        if (!in_array($mimeType, ['image/png', 'image/jpeg', 'application/pdf'])) {
            return null; // Unsupported file type
        }

        $base64EncodedSignature = base64_encode($signatureContent);
        return "data:{$mimeType};base64,{$base64EncodedSignature}";
    }
}
