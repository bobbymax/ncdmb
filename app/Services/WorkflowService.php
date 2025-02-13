<?php

namespace App\Services;

use App\Repositories\WorkflowRepository;

class WorkflowService extends BaseService
{
    public function __construct(WorkflowRepository $workflowRepository)
    {
        parent::__construct($workflowRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|sometimes|string|min:3',
            'type' => 'required|string|in:serialize,broadcast',
        ];
    }
}
