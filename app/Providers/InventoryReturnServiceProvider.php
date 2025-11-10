<?php

namespace App\Providers;

use App\Repositories\InventoryReturnRepository;
use App\Services\InventoryReturnService;
use App\Services\InventoryTransactionService;
use Illuminate\Support\ServiceProvider;

class InventoryReturnServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     */
    public function register(): void
    {
        $this->app->bind(InventoryReturnService::class, function ($app) {
            return new InventoryReturnService(
                $app->make(InventoryReturnRepository::class),
                $app->make(InventoryTransactionService::class),
            );
        });
    }
}
