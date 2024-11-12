<?php

namespace App\Http\Controllers;

use App\Services\ReserveService;

class ReserveController extends Controller
{
    public function __construct(ReserveService $reserveService) {
        parent::__construct($reserveService, 'Reserve');
    }
}