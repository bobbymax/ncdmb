<?php

namespace App\Providers;

use App\Http\Resources\ProductBrandResource;
use Illuminate\Support\ServiceProvider;
use App\Repositories\ProductBrandRepository;
use App\Services\ProductBrandService;

class ProductBrandServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProductBrandRepository to ProductBrandService
        $this->app->bind(ProductBrandService::class, function ($app) {
            $productBrandRepository = $app->make(ProductBrandRepository::class);
            $productBrandResource = $app->make(ProductBrandResource::class);

            return new ProductBrandService($productBrandRepository, $productBrandResource);
        });
    }
}