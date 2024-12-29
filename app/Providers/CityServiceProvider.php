<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\CityRepository;
use App\Services\CityService;

class CityServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the CityRepository to CityService
        $this->app->bind(CityService::class, function ($app) {
            $cityRepository = $app->make(CityRepository::class);

            return new CityService($cityRepository);
        });
    }
}
