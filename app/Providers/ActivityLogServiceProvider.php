<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ActivityLogRepository;
use App\Services\ActivityLogService;

class ActivityLogServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ActivityLogRepository to ActivityLogService
        $this->app->bind(ActivityLogService::class, function ($app) {
            $activityLogRepository = $app->make(ActivityLogRepository::class);

            return new ActivityLogService($activityLogRepository);
        });
    }
}
