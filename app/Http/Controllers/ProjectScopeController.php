<?php

namespace App\Http\Controllers;

use App\Services\ProjectScopeService;

class ProjectScopeController extends Controller
{
    public function __construct(ProjectScopeService $projectScopeService) {
        $this->service = $projectScopeService;
        $this->name = 'ProjectScope';
    }
}
