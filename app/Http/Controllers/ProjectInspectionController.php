<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProjectInspectionResource;
use App\Services\ProjectInspectionService;

class ProjectInspectionController extends BaseController
{
    public function __construct(ProjectInspectionService $projectInspectionService) {
        parent::__construct($projectInspectionService, 'ProjectInspection', ProjectInspectionResource::class);
    }
}
