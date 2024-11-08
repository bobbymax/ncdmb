<?php

namespace App\Services;

use App\Repositories\BoardProjectUtilizationRepository;

class BoardProjectUtilizationService extends BaseService
{
    public function __construct(BoardProjectUtilizationRepository $boardProjectUtilizationRepository)
    {
        $this->repository = $boardProjectUtilizationRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'board_project_id' => 'required|integer|exists:board_projects,id',
            'description' => 'required|string',
            'year' => 'required|integer',
            'period' => 'required|string|max:255',
            'capacity' => 'required|integer',
            'utilization' => 'required|integer',
            'revenue' => 'required|string|max:255',
        ];
    }
}
