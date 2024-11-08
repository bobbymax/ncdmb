<?php

namespace App\Http\Controllers;

use App\Services\LifActivityService;

class LifActivityController extends Controller
{
    public function __construct(LifActivityService $lifActivityService) {
        $this->service = $lifActivityService;
        $this->name = 'LifActivity';
    }
}
