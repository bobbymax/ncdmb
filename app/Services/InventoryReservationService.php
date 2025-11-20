<?php

namespace App\Services;

use App\Repositories\InventoryReservationRepository;

class InventoryReservationService extends BaseService
{
    public function __construct(InventoryReservationRepository $inventoryReservationRepository)
    {
        parent::__construct($inventoryReservationRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'requisition_item_id' => 'required|integer|exists:requisition_items,id',
            'product_id' => 'required|integer|exists:products,id',
            'product_measurement_id' => 'nullable|integer|exists:product_measurements,id',
            'location_id' => 'required|integer|exists:inventory_locations,id',
            'quantity_reserved' => 'required|numeric|min:0.0001',
            'reserved_until' => 'nullable|date|after:today',
            'status' => 'nullable|string|in:active,fulfilled,cancelled,expired',
            'reserved_by' => 'required|integer|exists:users,id',
            'notes' => 'nullable|string',
        ];
    }
}
