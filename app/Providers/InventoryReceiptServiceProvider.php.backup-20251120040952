<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\InventoryReceiptRepository;
use App\Services\InventoryReceiptService;

class InventoryReceiptServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the InventoryReceiptRepository to InventoryReceiptService
        $this->app->bind(InventoryReceiptService::class, function ($app) {
            $inventoryReceiptRepository = $app->make(InventoryReceiptRepository::class);

            return new InventoryReceiptService($inventoryReceiptRepository);
        });
    }
}
