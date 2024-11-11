<?php

namespace App\Services;

use App\Http\Resources\ProductBrandResource;
use App\Repositories\ProductBrandRepository;

class ProductBrandService extends BaseService
{
    public function __construct(
        ProductBrandRepository $productBrandRepository,
        ProductBrandResource $productBrandResource
    ) {
        parent::__construct($productBrandRepository, $productBrandResource);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
