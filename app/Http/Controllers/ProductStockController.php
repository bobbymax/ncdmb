<?php

namespace App\Http\Controllers;

use App\Services\ProductStockService;

class ProductStockController extends Controller
{
    public function __construct(ProductStockService $productStockService) {
        parent::__construct($productStockService, 'ProductStock');
    }
}
