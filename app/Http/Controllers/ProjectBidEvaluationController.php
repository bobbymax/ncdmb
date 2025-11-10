<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProjectBidEvaluationResource;
use App\Services\ProjectBidEvaluationService;

class ProjectBidEvaluationController extends BaseController
{
    public function __construct(ProjectBidEvaluationService $projectBidEvaluationService) {
        parent::__construct($projectBidEvaluationService, 'ProjectBidEvaluation', ProjectBidEvaluationResource::class);
    }
}
