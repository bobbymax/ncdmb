<?php

namespace App\Http\Resources;

use App\Models\Document;
use App\Models\DocumentDraft;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenditureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = $this->resolveExpenditureResource();
        return [
            ...parent::toArray($request),
            'fund' => [
                'department' => $this->fund->department->abv,
                'budget_code' => $this->fund->budgetCode->code,
                'sub_budget_head' => $this->fund->subBudgetHead->name,
                'type' => $this->fund->type,
                'total_approved_amount' => (float) $this->fund->total_approved_amount,
            ],
            'linked_document' => [
                'title' => $this->document->title,
                'ref' => $this->document->ref,
                'document_category_id' => $this->document->document_category_id,
                'published_at' => $this->document->created_at->format('Y-m-d'),
                'published_by' => [
                    'name' => "{$this->document->user->surname}, {$this->document->user->firstname} {$this->document->user->middlename}",
                    'staff_no' => $this->document->user->staff_no,
                    'department' => $this->document->user->department->abv,
                ],
                'approved_amount' => $this->document->approved_amount,
                'type' => $this->document->documentCategory->name,
                'resource_id' => $this->document->documentable_id,
                'resource_type' => $this->document->documentable_type,
            ]
        ];
    }

    private function getBeneficiary($resource)
    {
        if (!$resource) return null;
        return $resource->beneficiary;
    }

    private function resolveExpenditureResource(): ?JsonResource
    {
        if (!$this->expenditureable) {
            return null;
        }

        if ($this->expenditureable_type === DocumentDraft::class || $this->expenditureable_type === Document::class) {
            return null;
        }

        $modelClassName = class_basename($this->expenditureable);

        $resourceClass = "App\\Http\\Resources\\{$modelClassName}Resource";

        if (class_exists($resourceClass)) {
            return new $resourceClass($this->expenditureable);
        }

        return null;
    }
}
