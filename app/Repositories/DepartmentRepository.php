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
            'signatory_staff_id' => (isset($data['signatory_staff_id']) && $data['signatory_staff_id'] > 0) ? (int) $data['signatory_staff_id'] : null,
            'alternate_signatory_staff_id' => (isset($data['alternate_signatory_staff_id']) && $data['alternate_signatory_staff_id'] > 0) ? (int) $data['alternate_signatory_staff_id'] : null,
        ];
    }


}
