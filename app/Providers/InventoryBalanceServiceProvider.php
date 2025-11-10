<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\InventoryBalanceRepository;
use App\Services\InventoryBalanceService;

class InventoryBalanceServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the InventoryBalanceRepository to InventoryBalanceService
        $this->app->bind(InventoryBalanceService::class, function ($app) {
            $inventoryBalanceRepository = $app->make(InventoryBalanceRepository::class);

            return new InventoryBalanceService($inventoryBalanceRepository);
        });
    }
}
