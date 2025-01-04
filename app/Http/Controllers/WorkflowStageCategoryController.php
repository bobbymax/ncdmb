<?php

namespace App\Http\Controllers;


use App\Http\Resources\WorkflowStageCategoryResource;
use App\Services\WorkflowStageCategoryService;

class WorkflowStageCategoryController extends BaseController
{
    public function __construct(WorkflowStageCategoryService $workflowStageCategoryService) {
        parent::__construct($workflowStageCategoryService, 'WorkflowStageCategory', WorkflowStageCategoryResource::class);
    }
}
