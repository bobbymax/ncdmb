<?php

namespace App\Http\Controllers;

use App\Http\Resources\StoreSupplyResource;
use App\Services\StoreSupplyService;

class StoreSupplyController extends Controller
{
    public function __construct(StoreSupplyService $storeSupplyService) {
        parent::__construct($storeSupplyService, 'StoreSupply', StoreSupplyResource::class);
    }
}
