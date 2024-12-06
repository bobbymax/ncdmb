<?php

namespace App\Http\Controllers;


use App\Http\Resources\WorkflowResource;
use App\Services\WorkflowService;

class WorkflowController extends BaseController
{
    public function __construct(WorkflowService $workflowService) {
        parent::__construct($workflowService, 'Workflow', WorkflowResource::class);
    }
}
