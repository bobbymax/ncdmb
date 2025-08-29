<?php

namespace App\Http\Controllers;


use App\Http\Resources\InvoiceResource;
use App\Services\InvoiceService;

class InvoiceController extends BaseController
{
    public function __construct(InvoiceService $invoiceService) {
        parent::__construct($invoiceService, 'Invoice', InvoiceResource::class);
    }
}
