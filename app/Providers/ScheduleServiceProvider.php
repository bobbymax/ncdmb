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
    public function register(): void
    {
        // Bind the ScheduleRepository to ScheduleService
        $this->app->bind(ScheduleService::class, function ($app) {
            $scheduleRepository = $app->make(ScheduleRepository::class);

            return new ScheduleService($scheduleRepository);
        });
    }
}
