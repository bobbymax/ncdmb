<?php

namespace App\Http\Controllers;


use App\Http\Resources\InventoryReservationResource;
use App\Services\InventoryReservationService;

class InventoryReservationController extends BaseController
{
    public function __construct(InventoryReservationService $inventoryReservationService) {
        parent::__construct($inventoryReservationService, 'InventoryReservation', InventoryReservationResource::class);
    }
}
