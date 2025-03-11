<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentDraftResource extends JsonResource
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
            'template' => $this->getTemplate(),
            'draftable' => $this->resolveDraftableResource(),
            'signature' => $this->getSignature($this->signature),
            'order' => $this->tracker->order,
            'ref' => $this->document->ref,
            'authorising_officer' => $this->authorising_staff_id > 0 ? [
                'id' => $this->authorisingStaff->id,
                'name' => "{$this->authorisingStaff->surname}, {$this->authorisingStaff->firstname} {$this->authorisingStaff->middlename}",
                'staff_no' => $this->authorisingStaff->staff_no,
                'grade_level' => $this->authorisingStaff->gradeLevel->key,
                'email' => $this->authorisingStaff->email,
            ] : null,
        ];
    }

    protected function getSignature($path): ?string
    {
        if (empty($path) || !Storage::disk('public')->exists($path)) {
            return ""; // Return null for non-existent paths
        }

        $signatureContent = Storage::disk('public')->get($path);
        $mimeType = Storage::disk('public')->mimeType($path);

        // Only support specific MIME types
        if (!in_array($mimeType, ['image/png', 'image/jpeg', 'application/pdf'])) {
            return ""; // Unsupported file type
        }

        $base64EncodedSignature = base64_encode($signatureContent);
        return "data:{$mimeType};base64,{$base64EncodedSignature}";
    }

    private function getTemplate()
    {
        if ($this->document_type_id < 1) {
            return null;
        }

        if ($this->documentType && $this->documentType->file_template_id < 1) {
            return null;
        }

        return $this->documentType->template;
    }

    private function resolveDraftableResource(): ?JsonResource
    {
        if (!$this->documentDraftable) {
            return null;
        }

        $modelClassName = class_basename($this->documentDraftable);

        $resourceClass = "App\\Http\\Resources\\{$modelClassName}Resource";

        if (class_exists($resourceClass)) {
            return new $resourceClass($this->documentDraftable);
        }

        return null;
    }
}
