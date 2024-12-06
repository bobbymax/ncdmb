<?php

namespace App\Http\Controllers;

use App\Http\Resources\RequisitionResource;
use App\Services\RequisitionService;

class RequisitionController extends BaseController
{
    public function __construct(RequisitionService $requisitionService) {
        parent::__construct($requisitionService, 'Requisition', RequisitionResource::class);
    }
}
