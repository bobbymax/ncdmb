<?php

namespace App\Providers;

use App\Http\Resources\RequisitionResource;
use App\Repositories\RequisitionItemRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\RequisitionRepository;
use App\Services\RequisitionService;

class RequisitionServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the RequisitionRepository to RequisitionService
        $this->app->bind(RequisitionService::class, function ($app) {
            $requisitionRepository = $app->make(RequisitionRepository::class);
            $requisitionResource = $app->make(RequisitionResource::class);
            $requisitionItemRepository = $app->make(RequisitionItemRepository::class);

            return new RequisitionService($requisitionRepository, $requisitionResource, $requisitionItemRepository);
        });
    }
}
