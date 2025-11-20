<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\InventoryTransferRepository;
use App\Services\InventoryTransferService;

class InventoryTransferServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the InventoryTransferRepository to InventoryTransferService
        $this->app->bind(InventoryTransferService::class, function ($app) {
            $inventoryTransferRepository = $app->make(InventoryTransferRepository::class);

            return new InventoryTransferService($inventoryTransferRepository);
        });
    }
}
