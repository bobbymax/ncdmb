<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProjectInspectionRepository;
use App\Services\ProjectInspectionService;

class ProjectInspectionServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProjectInspectionRepository to ProjectInspectionService
        $this->app->bind(ProjectInspectionService::class, function ($app) {
            $projectInspectionRepository = $app->make(ProjectInspectionRepository::class);

            return new ProjectInspectionService($projectInspectionRepository);
        });
    }
}
