<?php

namespace App\Services;

use App\Repositories\ProductRepository;

class ProductService extends BaseService
{
    public function __construct(
        ProductRepository $productRepository
    ) {
        parent::__construct($productRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'product_category_id' => 'required|integer|exists:product_categories,id',
            'department_id' => 'required|integer|exists:departments,id',
            'product_brand_id' => 'sometimes|integer|exists:product_brands,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|min:3',
            'restock_qty' => 'sometimes|integer|min:1',
            'owner' => 'required|string|in:store,other',
            'out_of_stock' => 'sometimes|boolean',
            'is_blocked' => 'sometimes|boolean',
            'request_on_delivery' => 'required|boolean',
        ];
    }
}
