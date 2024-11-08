<?php

namespace App\Http\Controllers;

use App\Services\PrincipalInvestigatorService;

class PrincipalInvestigatorController extends Controller
{
    public function __construct(PrincipalInvestigatorService $principalInvestigatorService) {
        $this->service = $principalInvestigatorService;
        $this->name = 'PrincipalInvestigator';
    }
}
