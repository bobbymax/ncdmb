<?php

namespace App\Repositories;

use App\Models\InventoryTransfer;

class InventoryTransferRepository extends BaseRepository
{
    public function __construct(InventoryTransfer $inventoryTransfer) {
        parent::__construct($inventoryTransfer);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'reference' => $data['reference'] ?? $this->generate('reference', 'INV-TRF'),
            'status' => $data['status'] ?? 'pending',
            'transferred_at' => $data['transferred_at'] ?? now(),
        ];
    }
}
