<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;

class ProjectController extends BaseController
{
    public function __construct(ProjectService $projectService) {
        parent::__construct($projectService, 'Project', ProjectResource::class);
    }
}
