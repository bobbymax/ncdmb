<?php

namespace App\Providers;

use App\Http\Resources\ProductStockResource;
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
            $productStockResource = $app->make(ProductStockResource::class);

            return new ProductStockService($productStockRepository, $productStockResource);
        });
    }
}
