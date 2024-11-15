<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use App\Repositories\ProductStockRepository;
use App\Services\ProductStockService;

class ProductStockServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProductStockRepository to ProductStockService
        $this->app->bind(ProductStockService::class, function ($app) {
            $productStockRepository = $app->make(ProductStockRepository::class);

            return new ProductStockService($productStockRepository);
        });
    }
}
