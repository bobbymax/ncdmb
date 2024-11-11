<?php

namespace App\Http\Controllers;

use App\Services\StoreSupplyService;

class StoreSupplyController extends Controller
{
    public function __construct(StoreSupplyService $storeSupplyService) {
        parent::__construct($storeSupplyService, 'StoreSupply');
    }
}
