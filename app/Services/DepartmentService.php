<?php

namespace App\Services;

use App\Repositories\DepartmentRepository;

class DepartmentService extends BaseService
{
    public function __construct(DepartmentRepository $departmentRepository)
    {
        $this->repository = $departmentRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'abv' => 'required|string|max:255|unique:departments',
            'category' => 'required|string|max:255|in:unit,department,division,directorate',
        ];
    }
}
