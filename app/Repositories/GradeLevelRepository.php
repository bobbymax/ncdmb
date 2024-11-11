<?php

namespace App\Repositories;

use App\Models\GradeLevel;

class GradeLevelRepository extends BaseRepository
{
    public function __construct(GradeLevel $gradeLevel) {
        parent::__construct($gradeLevel);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
