<?php

namespace App\Http\Controllers;


use App\Http\Resources\InventoryIssueItemResource;
use App\Services\InventoryIssueItemService;

class InventoryIssueItemController extends BaseController
{
    public function __construct(InventoryIssueItemService $inventoryIssueItemService) {
        parent::__construct($inventoryIssueItemService, 'InventoryIssueItem', InventoryIssueItemResource::class);
    }
}
