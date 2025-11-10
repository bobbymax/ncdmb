<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\InventoryBatchRepository;
use App\Services\InventoryBatchService;

class InventoryBatchServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the InventoryBatchRepository to InventoryBatchService
        $this->app->bind(InventoryBatchService::class, function ($app) {
            $inventoryBatchRepository = $app->make(InventoryBatchRepository::class);

            return new InventoryBatchService($inventoryBatchRepository);
        });
    }
}
