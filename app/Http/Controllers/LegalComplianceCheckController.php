<?php

namespace App\Http\Controllers;


use App\Http\Resources\LegalComplianceCheckResource;
use App\Services\LegalComplianceCheckService;

class LegalComplianceCheckController extends BaseController
{
    public function __construct(LegalComplianceCheckService $legalComplianceCheckService) {
        parent::__construct($legalComplianceCheckService, 'LegalComplianceCheck', LegalComplianceCheckResource::class);
    }
}
