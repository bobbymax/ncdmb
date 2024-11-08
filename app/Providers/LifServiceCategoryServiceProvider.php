<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\LifServiceCategoryRepository;
use App\Services\LifServiceCategoryService;

class LifServiceCategoryServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the LifServiceCategoryRepository to LifServiceCategoryService
        $this->app->bind(LifServiceCategoryService::class, function ($app) {
            return new LifServiceCategoryService($app->make(LifServiceCategoryRepository::class));
        });
    }
}
