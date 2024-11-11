<?php

namespace App\Http\Controllers;

use App\Services\ProductCategoryService;

class ProductCategoryController extends Controller
{
    public function __construct(ProductCategoryService $productCategoryService) {
        parent::__construct($productCategoryService, 'Product Category');
    }
}
