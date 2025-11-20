<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\InventoryValuationRepository;
use App\Services\InventoryValuationService;

class InventoryValuationServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the InventoryValuationRepository to InventoryValuationService
        $this->app->bind(InventoryValuationService::class, function ($app) {
            $inventoryValuationRepository = $app->make(InventoryValuationRepository::class);

            return new InventoryValuationService($inventoryValuationRepository);
        });
    }
}
