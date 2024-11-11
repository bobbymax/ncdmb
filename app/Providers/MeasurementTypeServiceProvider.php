<?php

namespace App\Providers;

use App\Http\Resources\MeasurementTypeResource;
use Illuminate\Support\ServiceProvider;
use App\Repositories\MeasurementTypeRepository;
use App\Services\MeasurementTypeService;

class MeasurementTypeServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the MeasurementTypeRepository to MeasurementTypeService
        $this->app->bind(MeasurementTypeService::class, function ($app) {
            $measurementTypeRepository = $app->make(MeasurementTypeRepository::class);
            $measurementTypeResource = $app->make(MeasurementTypeResource::class);

            return new MeasurementTypeService($measurementTypeRepository, $measurementTypeResource);
        });
    }
}
