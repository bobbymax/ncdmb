<?php

namespace App\Services;


use App\Repositories\RequisitionItemRepository;

class RequisitionItemService extends BaseService
{
    public function __construct(
        RequisitionItemRepository $requisitionItemRepository
    ) {
        parent::__construct($requisitionItemRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'requisition_id' => 'required|integer|exists:requisitions,id',
            'product_id' => 'required|integer|exists:products,id',
            'quantity_requested' => 'required|integer|min:1',
            'quantity_issued' => 'sometimes|integer|min:0',
            'stock_balance' => 'sometimes|integer|min:0',
            'reason_for_denial' => 'sometimes|nullable|string|min:3',
        ];
    }
}
