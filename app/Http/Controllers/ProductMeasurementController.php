<?php

namespace App\Http\Controllers;

use App\Services\ProductMeasurementService;

class ProductMeasurementController extends Controller
{
    public function __construct(ProductMeasurementService $productMeasurementService) {
        parent::__construct($productMeasurementService, 'ProductMeasurement');
    }
}
