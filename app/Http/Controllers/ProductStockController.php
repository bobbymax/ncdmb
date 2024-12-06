<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductStockResource;
use App\Services\ProductStockService;

class ProductStockController extends BaseController
{
    public function __construct(ProductStockService $productStockService) {
        parent::__construct($productStockService, 'ProductStock', ProductStockResource::class);
    }
}
