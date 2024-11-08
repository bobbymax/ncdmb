<?php

namespace App\Http\Controllers;

use App\Services\ResearchDisseminationService;

class ResearchDisseminationController extends Controller
{
    public function __construct(ResearchDisseminationService $researchDisseminationService) {
        $this->service = $researchDisseminationService;
        $this->name = 'ResearchDissemination';
    }
}
