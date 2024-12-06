<?php

namespace App\Http\Controllers;


use App\Http\Resources\WorkflowStageResource;
use App\Services\WorkflowStageService;

class WorkflowStageController extends BaseController
{
    public function __construct(WorkflowStageService $workflowStageService) {
        parent::__construct($workflowStageService, 'WorkflowStage', WorkflowStageResource::class);
    }
}
