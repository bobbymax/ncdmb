<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductCategoryResource;
use App\Services\ProductCategoryService;

class ProductCategoryController extends BaseController
{
    public function __construct(ProductCategoryService $productCategoryService) {
        parent::__construct($productCategoryService, 'Product Category', ProductCategoryResource::class);
    }
}
