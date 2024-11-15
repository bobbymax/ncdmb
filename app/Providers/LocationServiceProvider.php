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
    public function register(): void
    {
        // Bind the LocationRepository to LocationService
        $this->app->bind(LocationService::class, function ($app) {
            $locationRepository = $app->make(LocationRepository::class);

            return new LocationService($locationRepository);
        });
    }
}
