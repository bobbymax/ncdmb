<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\CDActivityRepository;
use App\Services\CDActivityService;

class CDActivityServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the CDActivityRepository to CDActivityService
        $this->app->bind(CDActivityService::class, function ($app) {
            return new CDActivityService($app->make(CDActivityRepository::class));
        });
    }
}
