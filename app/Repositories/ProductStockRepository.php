<?php

namespace App\Repositories;

use App\Models\ProductStock;

class ProductStockRepository extends BaseRepository
{
    public function __construct(ProductStock $productStock) {
        parent::__construct($productStock);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
