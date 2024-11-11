<?php

namespace App\Http\Controllers;

use App\Services\GradeLevelService;

class GradeLevelController extends Controller
{
    public function __construct(GradeLevelService $gradeLevelService) {
        $this->service = $gradeLevelService;
        $this->name = 'GradeLevel';
    }
}
