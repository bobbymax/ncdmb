<?php

namespace App\Services;

use App\Repositories\InventoryIssueItemRepository;

class InventoryIssueItemService extends BaseService
{
    public function __construct(InventoryIssueItemRepository $inventoryIssueItemRepository)
    {
        parent::__construct($inventoryIssueItemRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'inventory_issue_id' => 'required|exists:inventory_issues,id',
            'requisition_item_id' => 'required|exists:requisition_items,id',
            'product_id' => 'required|exists:products,id',
            'product_measurement_id' => 'nullable|exists:product_measurements,id',
            'quantity_issued' => 'required|numeric|min:0.0001',
            'unit_cost' => 'nullable|numeric|min:0',
            'batch_id' => 'nullable|exists:inventory_batches,id',
        ];
    }
}
