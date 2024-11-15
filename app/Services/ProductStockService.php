<?php

namespace App\Services;

use App\Repositories\ProductStockRepository;

class ProductStockService extends BaseService
{
    public function __construct(
        ProductStockRepository $productStockRepository
    ) {
        parent::__construct($productStockRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'store_supply_id' => 'sometimes|integer|min:0|exists:store_supplies,id',
            'opening_stock_balance' => 'required|integer|min:0',
            'closing_stock_balance' => 'sometimes|integer|min:0',
            'out_of_stock' => 'sometimes|boolean',
        ];
    }
}
