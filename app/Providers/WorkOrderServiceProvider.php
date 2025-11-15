<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\WorkOrderRepository;
use App\Services\WorkOrderService;

class WorkOrderServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the WorkOrderRepository to WorkOrderService
        $this->app->bind(WorkOrderService::class, function ($app) {
            $workOrderRepository = $app->make(WorkOrderRepository::class);

            return new WorkOrderService($workOrderRepository);
        });
    }
}
