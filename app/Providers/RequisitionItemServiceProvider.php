<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\RequisitionItemRepository;
use App\Services\RequisitionItemService;

class RequisitionItemServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the RequisitionItemRepository to RequisitionItemService
        $this->app->bind(RequisitionItemService::class, function ($app) {
            $requisitionItemRepository = $app->make(RequisitionItemRepository::class);

            return new RequisitionItemService($requisitionItemRepository);
        });
    }
}
