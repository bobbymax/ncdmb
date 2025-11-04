<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProjectChangeRequestResource;
use App\Services\ProjectChangeRequestService;

class ProjectChangeRequestController extends BaseController
{
    public function __construct(ProjectChangeRequestService $projectChangeRequestService) {
        parent::__construct($projectChangeRequestService, 'ProjectChangeRequest', ProjectChangeRequestResource::class);
    }
}
