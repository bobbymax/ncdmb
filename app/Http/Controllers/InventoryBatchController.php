<?php

namespace App\Http\Controllers;


use App\Http\Resources\InventoryBatchResource;
use App\Services\InventoryBatchService;

class InventoryBatchController extends BaseController
{
    public function __construct(InventoryBatchService $inventoryBatchService) {
        parent::__construct($inventoryBatchService, 'InventoryBatch', InventoryBatchResource::class);
    }
}
