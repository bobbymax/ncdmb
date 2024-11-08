<?php

namespace App\Services;

use App\Repositories\ResearchLibraryRepository;

class ResearchLibraryService extends BaseService
{
    public function __construct(ResearchLibraryRepository $researchLibraryRepository)
    {
        $this->repository = $researchLibraryRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'r_n_d_project_id' => 'required|integer|exists:r_n_d_projects,id',
            'location' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'remark' => 'nullable|string|min:3',
            'type' => 'required|string|in:e-library,physical,other'
        ];
    }
}
