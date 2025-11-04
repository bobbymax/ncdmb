<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProjectFeasibilityStudyResource;
use App\Services\ProjectFeasibilityStudyService;

class ProjectFeasibilityStudyController extends BaseController
{
    public function __construct(ProjectFeasibilityStudyService $projectFeasibilityStudyService) {
        parent::__construct($projectFeasibilityStudyService, 'ProjectFeasibilityStudy', ProjectFeasibilityStudyResource::class);
    }
}
