<?php

namespace App\Providers;

use App\Repositories\InventoryAdjustmentRepository;
use App\Services\InventoryAdjustmentService;
use App\Services\InventoryTransactionService;
use Illuminate\Support\ServiceProvider;

class InventoryAdjustmentServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     */
    public function register(): void
    {
        $this->app->bind(InventoryAdjustmentService::class, function ($app) {
            return new InventoryAdjustmentService(
                $app->make(InventoryAdjustmentRepository::class),
                $app->make(InventoryTransactionService::class),
            );
        });
    }
}
