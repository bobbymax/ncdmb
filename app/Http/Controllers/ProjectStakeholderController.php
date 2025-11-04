<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProjectStakeholderResource;
use App\Services\ProjectStakeholderService;

class ProjectStakeholderController extends BaseController
{
    public function __construct(ProjectStakeholderService $projectStakeholderService) {
        parent::__construct($projectStakeholderService, 'ProjectStakeholder', ProjectStakeholderResource::class);
    }
}
