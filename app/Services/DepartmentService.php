<?php

namespace App\Services;


use App\Repositories\DepartmentRepository;

class DepartmentService extends BaseService
{
    public function __construct(DepartmentRepository $departmentRepository)
    {
        parent::__construct($departmentRepository);
    }

    public function rules($action = "store"): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'abv' => 'required|string|max:255',
            'department_payment_code' => 'sometimes|nullable|integer|max:10',
            'parentId' => 'sometimes|integer|min:0',
            'type' => 'required|string|in:directorate,division,department,unit',
            'bco' => 'sometimes|integer|min:0',
            'bo' => 'sometimes|integer|min:0',
            'director' => 'sometimes|integer|min:0',
        ];

        if ($action === "store") {
            $rules['abv'] .= '|unique:departments,abv';
        }

        return $rules;
    }
}
