<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\InventoryLocationRepository;
use App\Services\InventoryLocationService;

class InventoryLocationServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the InventoryLocationRepository to InventoryLocationService
        $this->app->bind(InventoryLocationService::class, function ($app) {
            $inventoryLocationRepository = $app->make(InventoryLocationRepository::class);

            return new InventoryLocationService($inventoryLocationRepository);
        });
    }
}
