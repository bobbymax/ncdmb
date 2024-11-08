<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ResearchFacilityRepository;
use App\Services\ResearchFacilityService;

class ResearchFacilityServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ResearchFacilityRepository to ResearchFacilityService
        $this->app->bind(ResearchFacilityService::class, function ($app) {
            return new ResearchFacilityService($app->make(ResearchFacilityRepository::class));
        });
    }
}
