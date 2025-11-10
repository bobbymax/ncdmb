<?php

namespace App\Http\Controllers;


use App\Http\Resources\InventoryBalanceResource;
use App\Services\InventoryBalanceService;

class InventoryBalanceController extends BaseController
{
    public function __construct(InventoryBalanceService $inventoryBalanceService) {
        parent::__construct($inventoryBalanceService, 'InventoryBalance', InventoryBalanceResource::class);
    }
}
