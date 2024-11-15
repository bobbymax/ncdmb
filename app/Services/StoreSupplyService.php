<?php

namespace App\Services;

use App\Repositories\StoreSupplyRepository;

class StoreSupplyService extends BaseService
{
    public function __construct(
        StoreSupplyRepository $storeSupplyRepository
    ) {
        parent::__construct($storeSupplyRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'project_contract_id' => 'required|integer|exists:project_contracts,id',
            'department_id' => 'required|integer|exists:departments,id',
            'product_id' => 'required|integer|exists:products,id',
            'product_measurement_id' => 'required|integer|exists:product_measurements,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'sometimes|numeric|min:0',
            'total_price' => 'sometimes|numeric|min:0',
            'delivery_date' => 'required|date',
            'expiration_date' => 'sometimes|nullable|date',
            'period_published' => 'required|string|in:Q1,Q2,Q3,Q4',
            'status' => 'required|string|in:unfulfilled,fulfilled,partial',
        ];
    }
}
