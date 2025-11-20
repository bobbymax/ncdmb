<?php

namespace App\Http\Controllers;


use App\Http\Resources\LegalClearanceResource;
use App\Services\LegalClearanceService;

class LegalClearanceController extends BaseController
{
    public function __construct(LegalClearanceService $legalClearanceService) {
        parent::__construct($legalClearanceService, 'LegalClearance', LegalClearanceResource::class);
    }
}
