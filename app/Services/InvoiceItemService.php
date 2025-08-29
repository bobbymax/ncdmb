<?php

namespace App\Services;

use App\Repositories\InvoiceItemRepository;

class InvoiceItemService extends BaseService
{
    public function __construct(InvoiceItemRepository $invoiceItemRepository)
    {
        parent::__construct($invoiceItemRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'invoice_id' => 'required|integer|exists:invoices,id',
            'description' => 'required|string|max:255',
            'qty' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:quoted,revised,delivered'
        ];
    }
}
