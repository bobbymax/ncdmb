<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\InventoryTransferItemRepository;
use App\Services\InventoryTransferItemService;

class InventoryTransferItemServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the InventoryTransferItemRepository to InventoryTransferItemService
        $this->app->bind(InventoryTransferItemService::class, function ($app) {
            $inventoryTransferItemRepository = $app->make(InventoryTransferItemRepository::class);

            return new InventoryTransferItemService($inventoryTransferItemRepository);
        });
    }
}
