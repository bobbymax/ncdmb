<?php

namespace App\Http\Controllers;

use App\Services\CDActivityService;

class CDActivityController extends Controller
{
    public function __construct(CDActivityService $cDActivityService) {
        $this->service = $cDActivityService;
        $this->name = 'CDActivity';
    }
}
