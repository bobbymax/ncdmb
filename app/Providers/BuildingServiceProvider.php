<?php

namespace App\Providers;

use App\Http\Resources\BuildingResource;
use Illuminate\Support\ServiceProvider;
use App\Repositories\BuildingRepository;
use App\Services\BuildingService;

class BuildingServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the BuildingRepository to BuildingService
        $this->app->bind(BuildingService::class, function ($app) {
            $buildingRepository = $app->make(BuildingRepository::class);
            $buildingResource = $app->make(BuildingResource::class);

            return new BuildingService($buildingRepository, $buildingResource);
        });
    }
}