<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProjectLifecycleStageRepository;
use App\Services\ProjectLifecycleStageService;

class ProjectLifecycleStageServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProjectLifecycleStageRepository to ProjectLifecycleStageService
        $this->app->bind(ProjectLifecycleStageService::class, function ($app) {
            $projectLifecycleStageRepository = $app->make(ProjectLifecycleStageRepository::class);

            return new ProjectLifecycleStageService($projectLifecycleStageRepository);
        });
    }
}
