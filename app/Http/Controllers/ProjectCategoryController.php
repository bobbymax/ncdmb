<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProjectCategoryResource;
use App\Services\ProjectCategoryService;

class ProjectCategoryController extends BaseController
{
    public function __construct(ProjectCategoryService $projectCategoryService) {
        parent::__construct($projectCategoryService, 'ProjectCategory', ProjectCategoryResource::class);
    }
}
