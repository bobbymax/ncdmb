<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\LifActivityRepository;
use App\Services\LifActivityService;

class LifActivityServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the LifActivityRepository to LifActivityService
        $this->app->bind(LifActivityService::class, function ($app) {
            return new LifActivityService($app->make(LifActivityRepository::class));
        });
    }
}
