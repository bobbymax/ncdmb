<?php

namespace App\Http\Controllers;


use App\Http\Resources\WorkOrderResource;
use App\Services\WorkOrderService;

class WorkOrderController extends BaseController
{
    public function __construct(WorkOrderService $workOrderService) {
        parent::__construct($workOrderService, 'WorkOrder', WorkOrderResource::class);
    }
}
