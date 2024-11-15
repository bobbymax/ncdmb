<?php

namespace App\Http\Controllers;

use App\Http\Resources\RequisitionItemResource;
use App\Services\RequisitionItemService;

class RequisitionItemController extends Controller
{
    public function __construct(RequisitionItemService $requisitionItemService) {
        parent::__construct($requisitionItemService, 'RequisitionItem', RequisitionItemResource::class);
    }
}
