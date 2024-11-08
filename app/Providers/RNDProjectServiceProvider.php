<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\RNDProjectRepository;
use App\Services\RNDProjectService;

class RNDProjectServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the RNDProjectRepository to RNDProjectService
        $this->app->bind(RNDProjectService::class, function ($app) {
            return new RNDProjectService($app->make(RNDProjectRepository::class));
        });
    }
}
