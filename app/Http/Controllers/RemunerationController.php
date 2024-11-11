<?php

namespace App\Http\Controllers;

use App\Services\RemunerationService;

class RemunerationController extends Controller
{
    public function __construct(RemunerationService $remunerationService) {
        parent::__construct($remunerationService, 'Remuneration');
    }
}
