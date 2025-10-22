<?php

namespace App\Http\Resources;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DocumentResource extends JsonResource
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
            'threads' => ThreadResource::collection($this->conversations),
            'documentable' => $this->resolveDocumentableResource(),
            'document_type' => new DocumentTypeResource($this->documentType),
            'document_category' => new DocumentCategoryResource($this->documentCategory),
            'workflow' => new WorkflowResource($this->workflow),
            'dept' => $this->department->abv,
            'owner' => [
                'id' => $this->user_id,
                'name' => "{$this->user->firstname} {$this->user->surname}",
                'email' => $this->user->email,
                'role' => $this->user->role->name,
                'department' => $this->user->department->abv,
                'groups' => $this->loadGroups($this->user->groups),
                'gradel_level' => $this->user->gradeLevel->key,
                'staff_no' => $this->user->staff_no,
            ],
            'drafts' => $this->drafts,
            'processes' => $this->workflow_id > 0 ? ProgressTrackerResource::collection($this->workflow->trackers) : [],
            'action' => $this->document_action_id > 0 ? [
                'id' => $this->document_action_id,
                'name' => $this->documentAction->name,
                'draft_status' => $this->documentAction->draft_status,
                'action_status' => $this->documentAction->action_status,
                'variant' => $this->documentAction->variant,
                'carder_id' => $this->documentAction->carder_id,
            ] : null,
            'uploads' => UploadResource::collection($this->getMergedUploads()),
// add all uploads back
            'pivot' => $this->whenPivotLoaded('document_hierarchy', function () {
                return [
                    'relationship_type' => $this->pivot->relationship_type,
                    'created_at' => $this->pivot->created_at,
                ];
            }),
            'amount' => $this->lastDraft() ? (float) $this->lastDraft()->amount : 0,
            'payments' => $this->documentable_type === "App\\Models\\PaymentBatch" ? PaymentResource::collection($this->documentable?->payments) : [],
        ];
    }

    private function getGroups(Group $group): array
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'label' => $group->label
        ];
    }

    private function loadGroups($groups): array
    {
        $loadedGroups = [];

        foreach ($groups as $group) {
            $loadedGroups[] = $this->getGroups($group);
        }

        return $loadedGroups;
    }

    private function getDocumentType(string $documentType): string
    {
        $pathArr = explode('\\', $documentType);
        return Str::slug(end($pathArr));
    }

    private function resolveDocumentableResource(): ?JsonResource
    {
        if (!$this->documentable) {
            return null;
        }

        $modelClassName = class_basename($this->documentable);

        $resourceClass = "App\\Http\\Resources\\{$modelClassName}Resource";

        if (class_exists($resourceClass)) {
            return new $resourceClass($this->documentable);
        }

        return null;
    }

    public function getLatestDraftsPerResource(): Collection
    {
        // Load all drafts and eager-load their relationships
        $allDrafts = $this->drafts()->get();

        // Group drafts by document_type_id
        $grouped = $allDrafts->groupBy('document_type_id');

        // Return only the latest draft per type, with history attached
        return $grouped->map(function (Collection $draftsOfType) {
            $latest = $draftsOfType->sortByDesc('version_number')->first();

            $history = $draftsOfType->filter(fn($d) => $d?->id !== $latest?->id);

            // Attach history
            $latest->history = $history->values();

            // Merge uploads from history and self
            $uploads = $history
                ->pluck('upload')   // Upload|null
                ->filter()          // Remove nulls
                ->values();

            // Add current draft's own upload if it exists
            if ($latest->upload) {
                $uploads->prepend($latest->upload);
            }

            // Attach the merged uploads to the latest draft (as a dynamic property)
            $latest->merged_uploads = $uploads;
            return $latest;
        })->values();
    }

    /**
     * Get merged uploads from current document and all linked documents
     *
     * @return \Illuminate\Support\Collection
     */
    private function getMergedUploads(): Collection
    {
        // Start with current document's uploads
        $mergedUploads = collect($this->uploads);

        // $this->relationLoaded('linkedDocuments') &&
        // Merge uploads from all linked documents
        if ($this->linkedDocuments) {
            foreach ($this->linkedDocuments as $linkedDocument) {
                if ($linkedDocument->uploads) {
                    $mergedUploads = $mergedUploads->merge($linkedDocument->uploads);
                }
            }
        }

        // Remove duplicates based on upload ID
        return $mergedUploads->unique('id');
    }
}
