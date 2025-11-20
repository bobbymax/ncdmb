<?php

namespace App\Repositories;

use App\Models\InventoryReceipt;

class InventoryReceiptRepository extends BaseRepository
{
    public function __construct(InventoryReceipt $inventoryReceipt) {
        parent::__construct($inventoryReceipt);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'reference' => $data['reference'] ?? $this->generate('reference', 'INV-RCP'),
            'status' => $data['status'] ?? 'pending',
            'received_at' => $data['received_at'] ?? now(),
        ];
    }
}
