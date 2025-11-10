<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProjectEvaluationCommitteeResource;
use App\Services\ProjectEvaluationCommitteeService;

class ProjectEvaluationCommitteeController extends BaseController
{
    public function __construct(ProjectEvaluationCommitteeService $projectEvaluationCommitteeService) {
        parent::__construct($projectEvaluationCommitteeService, 'ProjectEvaluationCommittee', ProjectEvaluationCommitteeResource::class);
    }
}
