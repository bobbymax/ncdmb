<?php

namespace App\Repositories;

use App\Models\Invoice;

class InvoiceRepository extends BaseRepository
{
    public function __construct(Invoice $invoice) {
        parent::__construct($invoice);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
