<?php

namespace App\Http\Controllers;


use App\Http\Resources\InventoryValuationResource;
use App\Services\InventoryValuationService;

class InventoryValuationController extends BaseController
{
    public function __construct(InventoryValuationService $inventoryValuationService) {
        parent::__construct($inventoryValuationService, 'InventoryValuation', InventoryValuationResource::class);
    }
}
