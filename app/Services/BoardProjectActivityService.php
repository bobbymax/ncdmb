<?php

namespace App\Services;

use App\Repositories\BoardProjectActivityRepository;

class BoardProjectActivityService extends BaseService
{
    public function __construct(BoardProjectActivityRepository $boardProjectActivityRepository)
    {
        $this->repository = $boardProjectActivityRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'board_project_id' => 'required|integer|exists:board_projects,id',
            'activity' => 'required|min:3',
            'year' => 'required|integer',
            'period' => 'required|string|max:255',
            'no_of_personnel' => 'required|integer',
            'man_hours' => 'required|string|max:255',
            'amount_spent' => 'required',
            'remarks' => 'required|string',
        ];
    }
}
