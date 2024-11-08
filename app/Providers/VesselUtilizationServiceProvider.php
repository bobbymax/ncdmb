<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\VesselUtilizationRepository;
use App\Services\VesselUtilizationService;

class VesselUtilizationServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the VesselUtilizationRepository to VesselUtilizationService
        $this->app->bind(VesselUtilizationService::class, function ($app) {
            return new VesselUtilizationService($app->make(VesselUtilizationRepository::class));
        });
    }
}
