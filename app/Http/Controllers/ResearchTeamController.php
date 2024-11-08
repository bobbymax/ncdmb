<?php

namespace App\Http\Controllers;

use App\Services\ResearchTeamService;

class ResearchTeamController extends Controller
{
    public function __construct(ResearchTeamService $researchTeamService) {
        $this->service = $researchTeamService;
        $this->name = 'ResearchTeam';
    }
}
