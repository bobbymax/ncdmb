<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\InventoryReceiptItemRepository;
use App\Services\InventoryReceiptItemService;

class InventoryReceiptItemServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the InventoryReceiptItemRepository to InventoryReceiptItemService
        $this->app->bind(InventoryReceiptItemService::class, function ($app) {
            $inventoryReceiptItemRepository = $app->make(InventoryReceiptItemRepository::class);

            return new InventoryReceiptItemService($inventoryReceiptItemRepository);
        });
    }
}
