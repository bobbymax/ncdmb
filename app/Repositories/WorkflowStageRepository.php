<?php

namespace App\Repositories;

use App\Models\WorkflowStage;

class WorkflowStageRepository extends BaseRepository
{
    public function __construct(WorkflowStage $workflowStage) {
        parent::__construct($workflowStage);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'department_id' => $data['department_id'] > 0 ? $data['department_id'] : null,
            'fallback_stage_id' => $data['fallback_stage_id'] > 0 ? $data['fallback_stage_id'] : null,
        ];
    }
}
