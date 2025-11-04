<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProjectRiskRepository;
use App\Services\ProjectRiskService;

class ProjectRiskServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProjectRiskRepository to ProjectRiskService
        $this->app->bind(ProjectRiskService::class, function ($app) {
            $projectRiskRepository = $app->make(ProjectRiskRepository::class);

            return new ProjectRiskService($projectRiskRepository);
        });
    }
}
