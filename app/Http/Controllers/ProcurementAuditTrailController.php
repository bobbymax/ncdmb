<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProcurementAuditTrailResource;
use App\Services\ProcurementAuditTrailService;

class ProcurementAuditTrailController extends BaseController
{
    public function __construct(ProcurementAuditTrailService $procurementAuditTrailService) {
        parent::__construct($procurementAuditTrailService, 'ProcurementAuditTrail', ProcurementAuditTrailResource::class);
    }
}
