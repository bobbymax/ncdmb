<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProgressTrackerRepository;
use App\Services\ProgressTrackerService;

class ProgressTrackerServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProgressTrackerRepository to ProgressTrackerService
        $this->app->bind(ProgressTrackerService::class, function ($app) {
            $progressTrackerRepository = $app->make(ProgressTrackerRepository::class);

            return new ProgressTrackerService($progressTrackerRepository);
        });
    }
}
