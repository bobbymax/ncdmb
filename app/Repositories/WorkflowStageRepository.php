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
        unset($data['selectedDocumentsRequired']);
        unset($data['selectedActions']);
        return $data;
    }
}
