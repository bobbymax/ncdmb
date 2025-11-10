<?php

namespace App\Providers;

use App\Repositories\InventoryBalanceRepository;
use App\Repositories\InventoryBatchRepository;
use App\Repositories\InventoryTransactionRepository;
use App\Services\InventoryTransactionService;
use Illuminate\Support\ServiceProvider;

class InventoryTransactionServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     */
    public function register(): void
    {
        $this->app->bind(InventoryTransactionService::class, function ($app) {
            return new InventoryTransactionService(
                $app->make(InventoryTransactionRepository::class),
                $app->make(InventoryBalanceRepository::class),
                $app->make(InventoryBatchRepository::class),
            );
        });
    }
}
