<?php

namespace App\Http\Controllers;


use App\Http\Resources\InventoryTransactionResource;
use App\Services\InventoryTransactionService;

class InventoryTransactionController extends BaseController
{
    public function __construct(InventoryTransactionService $inventoryTransactionService) {
        parent::__construct($inventoryTransactionService, 'InventoryTransaction', InventoryTransactionResource::class);
    }
}
