<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectMilestoneResource;
use App\Services\ProjectMilestoneService;

class ProjectMilestoneController extends Controller
{
    public function __construct(ProjectMilestoneService $projectMilestoneService) {
        parent::__construct($projectMilestoneService, 'Milestone', ProjectMilestoneResource::class);
    }
}
