<?php

namespace App\Http\Controllers;

use App\Services\RNDProjectService;

class RNDProjectController extends Controller
{
    public function __construct(RNDProjectService $rNDProjectService) {
        $this->service = $rNDProjectService;
        $this->name = 'RNDProject';
    }
}
