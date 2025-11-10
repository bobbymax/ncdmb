<?php

namespace App\Http\Controllers;


use App\Http\Resources\InventoryIssueResource;
use App\Services\InventoryIssueService;

class InventoryIssueController extends BaseController
{
    public function __construct(InventoryIssueService $inventoryIssueService) {
        parent::__construct($inventoryIssueService, 'InventoryIssue', InventoryIssueResource::class);
    }
}
