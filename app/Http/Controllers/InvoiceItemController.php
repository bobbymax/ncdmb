<?php

namespace App\Http\Controllers;


use App\Http\Resources\InvoiceItemResource;
use App\Services\InvoiceItemService;

class InvoiceItemController extends BaseController
{
    public function __construct(InvoiceItemService $invoiceItemService) {
        parent::__construct($invoiceItemService, 'InvoiceItem', InvoiceItemResource::class);
    }
}
