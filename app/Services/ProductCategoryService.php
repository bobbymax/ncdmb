<?php

namespace App\Services;

use App\Http\Resources\ProductCategoryResource;
use App\Repositories\ProductCategoryRepository;

class ProductCategoryService extends BaseService
{
    public function __construct(
        ProductCategoryRepository $productCategoryRepository,
        ProductCategoryResource $productCategoryResource
    ) {
        parent::__construct($productCategoryRepository, $productCategoryResource);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
