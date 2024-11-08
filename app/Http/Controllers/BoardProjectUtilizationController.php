<?php

namespace App\Http\Controllers;

use App\Services\BoardProjectUtilizationService;

class BoardProjectUtilizationController extends Controller
{
    public function __construct(BoardProjectUtilizationService $boardProjectUtilizationService) {
        $this->service = $boardProjectUtilizationService;
        $this->name = 'BoardProjectUtilization';
    }
}
