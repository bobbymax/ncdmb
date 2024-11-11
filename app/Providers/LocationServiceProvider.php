<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\LocationRepository;
use App\Services\LocationService;

class LocationServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the LocationRepository to LocationService
        $this->app->bind(LocationService::class, function ($app) {
            return new LocationService($app->make(LocationRepository::class));
        });
    }
}
