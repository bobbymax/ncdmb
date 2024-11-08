<?php

namespace App\Services;

use App\Repositories\ResearchDisseminationRepository;

class ResearchDisseminationService extends BaseService
{
    public function __construct(ResearchDisseminationRepository $researchDisseminationRepository)
    {
        $this->repository = $researchDisseminationRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'r_n_d_project_id' => 'required|integer|exists:r_n_d_projects,id',
            'dissemination_channel_id' => 'required|integer|exists:dissemination_channels,id',
            'title' => 'required|string|max:255',
            'publication_title' => 'nullable|string|max:255',
            'publication_date' => 'nullable|date',
        ];
    }
}
