<?php

namespace App\Http\Controllers;

use App\Services\LifInstitutionService;

class LifInstitutionController extends Controller
{
    public function __construct(LifInstitutionService $lifInstitutionService) {
        $this->service = $lifInstitutionService;
        $this->name = 'LifInstitution';
    }
}
