<?php

namespace App\Http\Controllers;

use App\Services\RequisitionService;

class RequisitionController extends Controller
{
    public function __construct(RequisitionService $requisitionService) {
        parent::__construct($requisitionService, 'Requisition');
    }
}
