<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ScheduleRepository;
use App\Services\ScheduleService;

class ScheduleServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ScheduleRepository to ScheduleService
        $this->app->bind(ScheduleService::class, function ($app) {
            return new ScheduleService($app->make(ScheduleRepository::class));
        });
    }
}
