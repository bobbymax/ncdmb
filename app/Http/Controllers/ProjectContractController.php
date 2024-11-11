<?php

namespace App\Http\Controllers;

use App\Services\ProjectContractService;

class ProjectContractController extends Controller
{
    public function __construct(ProjectContractService $projectContractService) {
        parent::__construct($projectContractService, 'Contract');
    }
}
