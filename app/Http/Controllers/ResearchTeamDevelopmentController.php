<?php

namespace App\Http\Controllers;

use App\Services\ResearchTeamDevelopmentService;

class ResearchTeamDevelopmentController extends Controller
{
    public function __construct(ResearchTeamDevelopmentService $researchTeamDevelopmentService) {
        $this->service = $researchTeamDevelopmentService;
        $this->name = 'ResearchTeamDevelopment';
    }
}
