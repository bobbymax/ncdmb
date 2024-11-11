<?php

namespace App\Http\Controllers;

use App\Services\ClaimService;

class ClaimController extends Controller
{
    public function __construct(ClaimService $claimService) {
        $this->service = $claimService;
        $this->name = 'Claim';
    }
}
