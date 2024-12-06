<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClaimResource;
use App\Services\ClaimService;

class ClaimController extends BaseController
{
    public function __construct(ClaimService $claimService) {
        parent::__construct($claimService, 'Claim', ClaimResource::class);
    }
}
