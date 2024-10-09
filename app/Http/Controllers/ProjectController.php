<?php

namespace App\Http\Controllers;

use App\Services\ProjectService;

class ProjectController extends Controller
{
    public function __construct(ProjectService $projectService) {
        $this->service = $projectService;
        $this->name = 'Project';
    }
}
