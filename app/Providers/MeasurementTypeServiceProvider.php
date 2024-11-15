<?php

namespace App\Providers;


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

            return new MeasurementTypeService($measurementTypeRepository);
        });
    }
}
