<?php

namespace App\Http\Controllers;


use App\Http\Resources\InventoryReturnResource;
use App\Services\InventoryReturnService;

class InventoryReturnController extends BaseController
{
    public function __construct(InventoryReturnService $inventoryReturnService) {
        parent::__construct($inventoryReturnService, 'InventoryReturn', InventoryReturnResource::class);
    }
}
