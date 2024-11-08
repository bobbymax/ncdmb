<?php

namespace App\Http\Controllers;

use App\Services\LifInstitutionServiceService;

class LifInstitutionServiceController extends Controller
{
    public function __construct(LifInstitutionServiceService $lifInstitutionServiceService) {
        $this->service = $lifInstitutionServiceService;
        $this->name = 'LifInstitutionService';
    }
}
