<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProjectPerformanceMetricRepository;
use App\Services\ProjectPerformanceMetricService;

class ProjectPerformanceMetricServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProjectPerformanceMetricRepository to ProjectPerformanceMetricService
        $this->app->bind(ProjectPerformanceMetricService::class, function ($app) {
            $projectPerformanceMetricRepository = $app->make(ProjectPerformanceMetricRepository::class);

            return new ProjectPerformanceMetricService($projectPerformanceMetricRepository);
        });
    }
}
