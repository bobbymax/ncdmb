<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\InventoryReservationRepository;
use App\Services\InventoryReservationService;

class InventoryReservationServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the InventoryReservationRepository to InventoryReservationService
        $this->app->bind(InventoryReservationService::class, function ($app) {
            $inventoryReservationRepository = $app->make(InventoryReservationRepository::class);

            return new InventoryReservationService($inventoryReservationRepository);
        });
    }
}
