<?php

namespace App\Http\Controllers;

use App\Services\ProjectContractService;

class ProjectContractController extends Controller
{
    public function __construct(ProjectContractService $projectContractService) {
        $this->service = $projectContractService;
        $this->name = 'ProjectContract';
    }
}
