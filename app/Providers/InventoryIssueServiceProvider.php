<?php

namespace App\Providers;

use App\Repositories\InventoryBalanceRepository;
use App\Repositories\InventoryIssueItemRepository;
use App\Repositories\InventoryIssueRepository;
use App\Services\InventoryIssueService;
use App\Services\InventoryTransactionService;
use Illuminate\Support\ServiceProvider;

class InventoryIssueServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     */
    public function register(): void
    {
        $this->app->bind(InventoryIssueService::class, function ($app) {
            return new InventoryIssueService(
                $app->make(InventoryIssueRepository::class),
                $app->make(InventoryIssueItemRepository::class),
                $app->make(InventoryTransactionService::class),
                $app->make(InventoryBalanceRepository::class),
            );
        });
    }
}
