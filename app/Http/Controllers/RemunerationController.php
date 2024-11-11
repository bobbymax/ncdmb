<?php

namespace App\Http\Controllers;

use App\Services\RemunerationService;

class RemunerationController extends Controller
{
    public function __construct(RemunerationService $remunerationService) {
        $this->service = $remunerationService;
        $this->name = 'Remuneration';
    }
}
