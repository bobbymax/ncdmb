<?php

namespace App\Repositories;

use App\Models\InvoiceItem;

class InvoiceItemRepository extends BaseRepository
{
    public function __construct(InvoiceItem $invoiceItem) {
        parent::__construct($invoiceItem);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
