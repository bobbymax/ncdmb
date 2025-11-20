<?php

namespace App\Http\Controllers;


use App\Http\Resources\LegalAuditTrailResource;
use App\Services\LegalAuditTrailService;

class LegalAuditTrailController extends BaseController
{
    public function __construct(LegalAuditTrailService $legalAuditTrailService) {
        parent::__construct($legalAuditTrailService, 'LegalAuditTrail', LegalAuditTrailResource::class);
    }
}
