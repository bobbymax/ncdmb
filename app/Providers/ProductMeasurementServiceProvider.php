<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use App\Repositories\ProductMeasurementRepository;
use App\Services\ProductMeasurementService;

class ProductMeasurementServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProductMeasurementRepository to ProductMeasurementService
        $this->app->bind(ProductMeasurementService::class, function ($app) {
            $productMeasurementRepository = $app->make(ProductMeasurementRepository::class);

            return new ProductMeasurementService($productMeasurementRepository);
        });
    }
}
