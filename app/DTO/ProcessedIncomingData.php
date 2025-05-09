<?php

namespace App\DTO;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

readonly class ProcessedIncomingData
{
    public function __construct(
        public int     $document_id,
        public int     $document_draft_id,
        public int     $document_action_id,
        public int     $document_resource_id,
        public int     $department_id,
        public int     $workflow_id,
        public int     $progress_tracker_id,
        public string  $service,
        public int     $user_id,
        public string  $mode,
        public array   $resources,
        public mixed   $state,
        public string  $type,
        public ?string $file,
        public ?int    $budget_year,
        public ?string $status,
        public ?int    $trigger_workflow_id,
        public ?string $method,
        public ?string $entity_type,
        public ?string $document_category,
        public ?string $document_type,
    ) {}

    /**
     * @throws ValidationException
     */
    public static function from(array $data): self
    {
        $validator = Validator::make($data, [
            'document_id' => 'required|integer|exists:documents,id',
            'document_draft_id' => 'required|integer|exists:document_drafts,id',
            'document_action_id' => 'required|integer|exists:document_actions,id',
            'document_resource_id' => 'required|integer|min:0',
            'department_id' => 'required|integer|exists:departments,id',
            'workflow_id' => 'required|integer|exists:workflows,id',
            'progress_tracker_id' => 'required|integer|exists:progress_trackers,id',
            'service' => 'required|string|max:255',
            'user_id' => 'required|integer|exists:users,id',
            'mode' => 'required|string|in:store,update',
            'budget_year' => 'sometimes|integer',
            'status' => 'sometimes|string|max:255',
            'trigger_workflow_id' => 'sometimes|integer',
            'method' => 'sometimes|string|max:255',
            'entity_type' => 'sometimes|string|max:255',
            'document_category' => 'sometimes|string|max:255',
            'document_type' => 'sometimes|string|max:255',
            'type' => 'required|string|max:255|in:staff,third-party',
            'resources' => 'required|array',
            'state' => 'sometimes',
            'file' => 'sometimes|nullable|string'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        return new self(
            $validated['document_id'],
            $validated['document_draft_id'],
            $validated['document_action_id'],
            $validated['document_resource_id'],
            $validated['department_id'],
            $validated['workflow_id'],
            $validated['progress_tracker_id'],
            $validated['service'],
            $validated['user_id'],
            $validated['mode'],
            $validated['resources'],
            $validated['state'],
            $validated['type'] ?? 'staff',
            $validated['file'] ?? null,
            $validated['budget_year'],
            $validated['status'] ?? null,
            $validated['trigger_workflow_id'] ?? 0,
            $validated['method'] ?? 'consolidate',
            $validated['entity_type'] ?? 'staff',
            $validated['document_category'] ?? null,
            $validated['document_type'] ?? null,
        );
    }
}
