<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProjectIssueResource;
use App\Services\ProjectIssueService;

class ProjectIssueController extends BaseController
{
    public function __construct(ProjectIssueService $projectIssueService) {
        parent::__construct($projectIssueService, 'ProjectIssue', ProjectIssueResource::class);
    }
}
