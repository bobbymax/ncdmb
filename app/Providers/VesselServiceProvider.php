<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\VesselRepository;
use App\Services\VesselService;

class VesselServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the VesselRepository to VesselService
        $this->app->bind(VesselService::class, function ($app) {
            return new VesselService($app->make(VesselRepository::class));
        });
    }
}
