<?php

namespace App\Http\Controllers;

use App\Services\RequisitionItemService;

class RequisitionItemController extends Controller
{
    public function __construct(RequisitionItemService $requisitionItemService) {
        parent::__construct($requisitionItemService, 'RequisitionItem');
    }
}
