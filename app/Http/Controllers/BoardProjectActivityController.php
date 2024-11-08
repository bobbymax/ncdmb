<?php

namespace App\Http\Controllers;

use App\Services\BoardProjectActivityService;

class BoardProjectActivityController extends Controller
{
    public function __construct(BoardProjectActivityService $boardProjectActivityService) {
        $this->service = $boardProjectActivityService;
        $this->name = 'BoardProjectActivity';
    }
}
