<?php

namespace App\Http\Controllers;


use App\Http\Resources\InventoryAdjustmentResource;
use App\Services\InventoryAdjustmentService;

class InventoryAdjustmentController extends BaseController
{
    public function __construct(InventoryAdjustmentService $inventoryAdjustmentService) {
        parent::__construct($inventoryAdjustmentService, 'InventoryAdjustment', InventoryAdjustmentResource::class);
    }
}
