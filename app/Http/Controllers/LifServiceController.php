<?php

namespace App\Http\Controllers;

use App\Services\LifServiceService;

class LifServiceController extends Controller
{
    public function __construct(LifServiceService $lifServiceService) {
        $this->service = $lifServiceService;
        $this->name = 'LifService';
    }
}
