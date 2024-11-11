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
            'abv' => 'required|string|max:255|unique:departments,abv',
            'department_payment_code' => 'required|string|max:10',
            'parentId' => 'required|integer|min:0',
            'type' => 'required|string|in:directorate,division,department,unit',
            'bco' => 'sometimes|integer|min:0',
            'bo' => 'sometimes|integer|min:0',
            'director' => 'sometimes|integer|min:0',
        ];
    }
}
