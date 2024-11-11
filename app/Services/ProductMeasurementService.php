<?php

namespace App\Services;

use App\Http\Resources\ProductMeasurementResource;
use App\Repositories\ProductMeasurementRepository;

class ProductMeasurementService extends BaseService
{
    public function __construct(
        ProductMeasurementRepository $productMeasurementRepository,
        ProductMeasurementResource $productMeasurementResource
    ) {
        parent::__construct($productMeasurementRepository, $productMeasurementResource);
    }

    public function rules($action = "store"): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'product_measurement_id' => 'required|integer|exists:product_measurements,id',
            'quantity' => 'required|integer|min:1',
        ];
    }
}
