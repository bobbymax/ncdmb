<?php

namespace App\Http\Controllers;

use App\Services\EQEmployeeService;

class EQEmployeeController extends Controller
{
    public function __construct(EQEmployeeService $eQEmployeeService) {
        $this->service = $eQEmployeeService;
        $this->name = 'EQEmployee';
    }
}
