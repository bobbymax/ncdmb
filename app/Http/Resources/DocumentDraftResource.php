<?php

namespace App\Http\Resources;

use App\Helpers\WorkflowHelper;
use App\Models\Document;
use App\Models\DocumentDraft;
use Carbon\Carbon;
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
            'staff' => $this->created_by_user_id > 0 ? [
                'id' => $this->user->id,
                'name' => "{$this->user->surname}, {$this->user->firstname} {$this->user->middlename}",
                'staff_no' => $this->user->staff_no,
                'grade_level' => $this->user->gradeLevel->key,
                'email' => $this->user->email,
            ] : null,
            'action' => $this->document_action_id > 0 ? [
                'name' => $this->documentAction->name,
                'draft_status' => $this->documentAction->draft_status,
                'action_status' => $this->documentAction->action_status,
                'variant' => $this->documentAction->variant,
                'carder_id' => $this->documentAction->carder_id,
            ] : null,
            'approval' => $this->approval ? new SignatureResource($this->approval) : null,
            'message' => WorkflowHelper::generateDraftMessage($this),
            'history' => $this->when(isset($this->history), function () {
                return collect($this->history)->map(fn ($draft) => [
                    'id' => $draft->id,
                    'version_number' => $draft->version_number,
                    'approval' => $draft->approval ? new SignatureResource($draft->approval) : null,
                    'amount' => $draft->amount,
                    'authorising_officer' => $draft->authorisingStaff,
                    'staff' => $draft->user,
                    'created_at' => Carbon::parse($draft->created_at)->diffForHumans(),
                    'upload' => $draft->upload ? new UploadResource($draft->upload) : null,
                ]);
            }),
            'upload' => $this->upload ? new UploadResource($this->upload) : null,
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

        if ($this->document_draftable_type === DocumentDraft::class || $this->document_draftable_type === Document::class) {
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
