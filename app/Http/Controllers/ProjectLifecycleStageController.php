<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProjectLifecycleStageResource;
use App\Services\ProjectLifecycleStageService;

class ProjectLifecycleStageController extends BaseController
{
    public function __construct(ProjectLifecycleStageService $projectLifecycleStageService) {
        parent::__construct($projectLifecycleStageService, 'ProjectLifecycleStage', ProjectLifecycleStageResource::class);
    }
}
