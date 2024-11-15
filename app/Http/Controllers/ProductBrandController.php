<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductBrandResource;
use App\Services\ProductBrandService;

class ProductBrandController extends Controller
{
    public function __construct(ProductBrandService $productBrandService) {
        parent::__construct($productBrandService, 'Product Brand', ProductBrandResource::class);
    }
}
