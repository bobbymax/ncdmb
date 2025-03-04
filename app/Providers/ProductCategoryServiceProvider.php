<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProductCategoryRepository;
use App\Services\ProductCategoryService;

class ProductCategoryServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProductCategoryRepository to ProductCategoryService
        $this->app->bind(ProductCategoryService::class, function ($app) {
            $productCategoryRepository = $app->make(ProductCategoryRepository::class);

            return new ProductCategoryService($productCategoryRepository);
        });
    }
}
