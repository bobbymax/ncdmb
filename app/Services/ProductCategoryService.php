<?php

namespace App\Services;

use App\Repositories\ProductCategoryRepository;

class ProductCategoryService extends BaseService
{
    public function __construct(
        ProductCategoryRepository $productCategoryRepository
    ) {
        parent::__construct($productCategoryRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
