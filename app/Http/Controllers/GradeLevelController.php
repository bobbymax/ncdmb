<?php

namespace App\Http\Controllers;

use App\Services\GradeLevelService;

class GradeLevelController extends Controller
{
    public function __construct(GradeLevelService $gradeLevelService) {
        parent::__construct($gradeLevelService, 'Grade Level');
    }
}
