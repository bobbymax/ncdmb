<?php

namespace App\Http\Controllers;

use App\Services\AllowanceService;

class AllowanceController extends Controller
{
    public function __construct(AllowanceService $allowanceService) {
        $this->service = $allowanceService;
        $this->name = 'Allowance';
    }
}
