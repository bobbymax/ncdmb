<?php

namespace App\Http\Controllers;

use App\Http\Resources\AllowanceResource;
use App\Services\AllowanceService;

class AllowanceController extends BaseController
{
    public function __construct(AllowanceService $allowanceService) {
        parent::__construct($allowanceService, 'Allowance', AllowanceResource::class);
    }
}
