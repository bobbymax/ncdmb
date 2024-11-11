<?php

namespace App\Http\Controllers;

use App\Services\ProjectMilestoneService;

class ProjectMilestoneController extends Controller
{
    public function __construct(ProjectMilestoneService $projectMilestoneService) {
        $this->service = $projectMilestoneService;
        $this->name = 'ProjectMilestone';
    }
}
