<?php

namespace App\Providers;


use App\Repositories\ProductMeasurementRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\ProductRepository;
use App\Services\ProductService;

class ProductServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProductRepository to ProductService
        $this->app->bind(ProductService::class, function ($app) {
            $productRepository = $app->make(ProductRepository::class);
            $productMeasurementRepository = $app->make(ProductMeasurementRepository::class);

            return new ProductService($productRepository, $productMeasurementRepository);
        });
    }
}
