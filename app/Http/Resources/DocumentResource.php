<?php

namespace App\Http\Resources;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
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
            'documentable' => $this->resolveDocumentableResource(),
            'drafts' => DocumentDraftResource::collection($this->drafts()->orderBy('created_at', 'desc')->get()),
            'document_template' => $this->getDocumentType($this->documentable_type),
            'document_type' => new DocumentTypeResource($this->documentType),
            'workflow' => new WorkflowResource($this->workflow),
            'owner' => [
                'id' => $this->user_id,
                'name' => "{$this->user->firstname} {$this->user->surname}",
                'email' => $this->user->email,
                'role' => $this->user->role->name,
                'department' => $this->user->department->abv,
                'groups' => $this->loadGroups($this->user->groups),
                'gradel_level' => $this->user->gradeLevel->key
            ]
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
}
