<?php

namespace App\Http\Controllers;

use App\Services\EQApprovalService;

class EQApprovalController extends Controller
{
    public function __construct(EQApprovalService $eQApprovalService) {
        $this->service = $eQApprovalService;
        $this->name = 'EQApproval';
    }
}
