<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ActivityRepository;
use App\Services\ActivityService;

class ActivityServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ActivityRepository to ActivityService
        $this->app->bind(ActivityService::class, function ($app) {
            return new ActivityService($app->make(ActivityRepository::class));
        });
    }
}
