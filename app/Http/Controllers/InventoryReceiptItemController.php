<?php

namespace App\Http\Controllers;


use App\Http\Resources\InventoryReceiptItemResource;
use App\Services\InventoryReceiptItemService;

class InventoryReceiptItemController extends BaseController
{
    public function __construct(InventoryReceiptItemService $inventoryReceiptItemService) {
        parent::__construct($inventoryReceiptItemService, 'InventoryReceiptItem', InventoryReceiptItemResource::class);
    }
}
