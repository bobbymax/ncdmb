<?php

namespace App\Http\Controllers;

use App\Services\ProductService;

class ProductController extends Controller
{
    public function __construct(ProductService $productService) {
        parent::__construct($productService, 'Product');
    }
}
