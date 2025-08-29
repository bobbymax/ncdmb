<?php

namespace App\Services;

use App\Repositories\InvoiceItemRepository;
use App\Repositories\InvoiceRepository;
use Illuminate\Support\Facades\DB;

class InvoiceService extends BaseService
{
    protected InvoiceItemRepository $invoiceItemRepository;
    public function __construct(InvoiceRepository $invoiceRepository, InvoiceItemRepository $invoiceItemRepository)
    {
        parent::__construct($invoiceRepository);
        $this->invoiceItemRepository = $invoiceItemRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'invoiceable_id' => 'required|integer',
            'invoiceable_type' => 'required|string',
            'sub_total_amount' => 'required|numeric',
            'service_charge' => 'required|numeric',
            'grand_total_amount' => 'required|numeric',
            'items' => 'required|array',
            'currency' => 'required|string|in:NGN,USD,EUR,GBP,YEN,NA',
            'status' => 'required|string|in:pending,fulfilled,partial,defaulted',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $invoice = parent::store([
                ...$data,
                'invoice_number' => $this->generate('invoice_number', 'INV')
            ]);

            if ($invoice && is_array($data['items']) && !empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $this->invoiceItemRepository->create([
                        ...$item,
                        'invoice_id' => $invoice->id,
                    ]);
                }
            }

            return $invoice;
        });
    }
}
