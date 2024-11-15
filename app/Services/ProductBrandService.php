<?php

namespace App\Services;

use App\Repositories\ProductBrandRepository;

class ProductBrandService extends BaseService
{
    public function __construct(
        ProductBrandRepository $productBrandRepository
    ) {
        parent::__construct($productBrandRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
