<?php

namespace App\Support\Builders;

use App\Repositories\DocumentRepository;
use App\Repositories\WorkflowRepository;
use Illuminate\Support\Facades\Auth;

class DocumentDataBuilder
{
    public function __construct(
        protected DocumentRepository $documentRepository,
        protected WorkflowRepository $workflowRepository,
    ) {}

    public function build(
        array $data,
        object $resource,
        int $departmentId,
        string $title,
        string $description
    ): array
    {
        return [
            'user_id' => Auth::id(),
            'workflow_id' => $data['workflow_id'],
            'department_id' => $departmentId,
            'document_category_id' => $data['document_category_id'],
            'document_reference_id' => $data['document_reference_id'] ?? 0,
            'document_type_id' => $data['document_type_id'],
            'document_action_id' => $data['document_action_id'] ?? 0,
            'progress_tracker_id' => $this->getFirstTrackerId($data['workflow_id']),
            'vendor_id' => $data['vendor_id'] ?? 0,
            'title' => $title,
            'description' => $description,
            'documentable_id' => $resource->id,
            'documentable_type' => get_class($resource),
            'ref' => $this->documentRepository->generateRef($departmentId, $resource->code),
        ];
    }

    protected function getFirstTrackerId(int $workflowId)
    {
        $workflow = $this->workflowRepository->find($workflowId);

        if (!$workflow) {
            return 0;
        }
        $tracker = $workflow->trackers()->where('order', 1)->first();

        return $tracker?->id ?? 0;
    }
}
