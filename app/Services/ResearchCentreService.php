<?php

namespace App\Services;

use App\Repositories\ResearchCentreRepository;

class ResearchCentreService extends BaseService
{
    public function __construct(ResearchCentreRepository $researchCentreRepository)
    {
        $this->repository = $researchCentreRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'r_n_d_project_id' => 'required|integer|exists:r_n_d_projects,id',
            'address' => 'nullable|string|min:3',
            'phone' => 'nullable|string|min:3|unique:research_centres,phone',
            'email' => 'nullable|string|email|max:255|unique:research_centres,email',
            'category' => 'required|string|max:255|in:university,research-centre,other'
        ];
    }
}
