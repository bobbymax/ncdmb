<?php

namespace App\Repositories;

use App\Models\ProductMeasurement;

class ProductMeasurementRepository extends BaseRepository
{
    public function __construct(ProductMeasurement $productMeasurement) {
        parent::__construct($productMeasurement);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
