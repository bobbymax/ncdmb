<?php

namespace App\Http\Controllers;


use App\Http\Resources\InventoryReceiptResource;
use App\Services\InventoryReceiptService;

class InventoryReceiptController extends BaseController
{
    public function __construct(InventoryReceiptService $inventoryReceiptService) {
        parent::__construct($inventoryReceiptService, 'InventoryReceipt', InventoryReceiptResource::class);
    }
}
