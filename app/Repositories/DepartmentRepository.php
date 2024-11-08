<?php

namespace App\Repositories;

use App\Models\Department;

class DepartmentRepository extends BaseRepository
{
    public function __construct(Department $department) {
        parent::__construct($department);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
