<?php

namespace App\Repositories;

use App\Models\InventoryReservation;

class InventoryReservationRepository extends BaseRepository
{
    public function __construct(InventoryReservation $inventoryReservation) {
        parent::__construct($inventoryReservation);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'status' => $data['status'] ?? 'active',
            'reserved_by' => $data['reserved_by'] ?? \Illuminate\Support\Facades\Auth::id(),
        ];
    }
}
