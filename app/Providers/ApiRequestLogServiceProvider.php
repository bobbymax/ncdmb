<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ApiRequestLogRepository;
use App\Services\ApiRequestLogService;

class ApiRequestLogServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ApiRequestLogRepository to ApiRequestLogService
        $this->app->bind(ApiRequestLogService::class, function ($app) {
            $apiRequestLogRepository = $app->make(ApiRequestLogRepository::class);

            return new ApiRequestLogService($apiRequestLogRepository);
        });
    }
}
