<?php

namespace App\Services;

use App\Repositories\ProjectCategoryRepository;

class ProjectCategoryService extends BaseService
{
    public function __construct(ProjectCategoryRepository $projectCategoryRepository)
    {
        parent::__construct($projectCategoryRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ];
    }
}
