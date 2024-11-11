<?php

namespace App\Services;

use App\Repositories\GradeLevelRepository;

class GradeLevelService extends BaseService
{
    public function __construct(GradeLevelRepository $gradeLevelRepository)
    {
        $this->repository = $gradeLevelRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'key' => 'required|string|unique:grade_levels,key',
            'name' => 'required|string|max:255',
            'type' => 'required|in:system,board',
        ];
    }
}
