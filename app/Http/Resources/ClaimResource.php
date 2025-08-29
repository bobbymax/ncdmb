<?php

namespace App\Http\Resources;

use App\Models\User;
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
            'document_id' => $this->document->id,
            'department_name' => $this->sponsoring_department_id > 0 ? $this->sponsor?->abv : $this->department->abv,
            'expenses' => ExpenseResource::collection($this->expenses),
            'total_amount_spent' => (float) $this->total_amount_spent,
            'total_amount_approved' => (float) $this->total_amount_approved,
            'total_amount_retired' => (float) $this->total_amount_retired,
            'category_label' => $this->category ? $this->category->label : "",
            'owner' => [
                'staff_no' => $this->staff?->staff_no ?? "none",
                'grade_level' => $this->staff?->gradeLevel?->key ?? "none",
                'name' => "{$this->staff?->surname}, {$this->staff?->firstname} {$this->staff?->middlename}"
            ],
            'beneficiary' => [
                'payment_number' => $this->staff?->staff_no ?? "none",
                'classification' => $this->staff?->gradeLevel?->key ?? "none",
                'name' => "{$this->staff?->surname}, {$this->staff?->firstname} {$this->staff?->middlename}",
                'resource_type' => User::class,
                'beneficiary_id' => $this->user_id,
            ],
            'claimant_signature' => $this->getClaimSignature($this->claimant_signature),
            'approval_signature' => $this->getClaimSignature($this->approval_signature),
            'authorising_officer' => $this->authorising_staff_id > 0 ? [
                'id' => $this->authorisingOfficer->id,
                'name' => "{$this->authorisingOfficer->surname}, {$this->authorisingOfficer->firstname} {$this->authorisingOfficer->middlename}",
                'staff_no' => $this->authorisingOfficer->staff_no,
                'grade_level' => $this->authorisingOfficer->gradeLevel->key,
                'email' => $this->authorisingOfficer->email,
            ] : null,
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
