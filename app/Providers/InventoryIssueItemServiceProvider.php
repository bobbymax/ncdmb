<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\InventoryIssueItemRepository;
use App\Services\InventoryIssueItemService;

class InventoryIssueItemServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the InventoryIssueItemRepository to InventoryIssueItemService
        $this->app->bind(InventoryIssueItemService::class, function ($app) {
            $inventoryIssueItemRepository = $app->make(InventoryIssueItemRepository::class);

            return new InventoryIssueItemService($inventoryIssueItemRepository);
        });
    }
}
