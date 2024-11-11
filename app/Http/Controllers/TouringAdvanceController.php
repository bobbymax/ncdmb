<?php

namespace App\Http\Controllers;

use App\Services\TouringAdvanceService;

class TouringAdvanceController extends Controller
{
    public function __construct(TouringAdvanceService $touringAdvanceService) {
        $this->service = $touringAdvanceService;
        $this->name = 'TouringAdvance';
    }
}
