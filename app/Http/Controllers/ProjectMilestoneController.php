<?php

namespace App\Http\Controllers;

use App\Services\ProjectMilestoneService;

class ProjectMilestoneController extends Controller
{
    public function __construct(ProjectMilestoneService $projectMilestoneService) {
        parent::__construct($projectMilestoneService, 'Milestone');
    }
}
