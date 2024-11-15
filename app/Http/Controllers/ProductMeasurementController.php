<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductMeasurementResource;
use App\Services\ProductMeasurementService;

class ProductMeasurementController extends Controller
{
    public function __construct(ProductMeasurementService $productMeasurementService) {
        parent::__construct($productMeasurementService, 'ProductMeasurement', ProductMeasurementResource::class);
    }
}
