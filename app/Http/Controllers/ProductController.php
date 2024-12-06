<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Services\ProductService;

class ProductController extends BaseController
{
    public function __construct(ProductService $productService) {
        parent::__construct($productService, 'Product', ProductResource::class);
    }
}
