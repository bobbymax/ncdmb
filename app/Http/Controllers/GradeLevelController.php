<?php

namespace App\Http\Controllers;

use App\Http\Resources\GradeLevelResource;
use App\Services\GradeLevelService;

class GradeLevelController extends BaseController
{
    public function __construct(GradeLevelService $gradeLevelService) {
        parent::__construct($gradeLevelService, 'Grade Level', GradeLevelResource::class);
    }
}
