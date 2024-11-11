<?php

namespace App\Repositories;

use App\Models\Department;
use Illuminate\Support\Str;

class DepartmentRepository extends BaseRepository
{
    public function __construct(Department $department) {
        parent::__construct($department);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['name']),
        ];
    }
}
