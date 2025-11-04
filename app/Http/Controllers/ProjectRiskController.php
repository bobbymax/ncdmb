<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProjectRiskResource;
use App\Services\ProjectRiskService;

class ProjectRiskController extends BaseController
{
    public function __construct(ProjectRiskService $projectRiskService) {
        parent::__construct($projectRiskService, 'ProjectRisk', ProjectRiskResource::class);
    }
}
