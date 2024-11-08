<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\LifServiceRepository;
use App\Services\LifServiceService;

class LifServiceServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the LifServiceRepository to LifServiceService
        $this->app->bind(LifServiceService::class, function ($app) {
            return new LifServiceService($app->make(LifServiceRepository::class));
        });
    }
}
